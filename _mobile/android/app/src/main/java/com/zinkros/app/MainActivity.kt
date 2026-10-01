package com.zinkros.app

import android.annotation.SuppressLint
import android.content.Intent
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.NetworkRequest
import android.os.Bundle
import android.view.KeyEvent
import android.view.View
import android.webkit.*
import android.widget.ProgressBar
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.net.toUri
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private lateinit var swipeRefresh: SwipeRefreshLayout
    private lateinit var connectivityManager: ConnectivityManager

    private var isOffline = false
    private var networkCallback: ConnectivityManager.NetworkCallback? = null

    private val baseUrl = "https://zinkros.my.id/"
    private val appHost = "zinkros.my.id"

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        connectivityManager = getSystemService(CONNECTIVITY_SERVICE) as ConnectivityManager
        webView = findViewById(R.id.webView)
        progressBar = findViewById(R.id.progressBar)
        swipeRefresh = findViewById(R.id.swipeRefresh)

        setupWebView()
        setupSwipeRefresh()
        registerNetworkCallback()

        // Mendaftarkan Callback Back Button modern
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (webView.canGoBack()) {
                    webView.goBack()
                } else {
                    AlertDialog.Builder(this@MainActivity)
                        .setTitle("Keluar")
                        .setMessage("Keluar dari aplikasi?")
                        .setPositiveButton("Ya") { _, _ -> finish() }
                        .setNegativeButton("Tidak", null)
                        .show()
                }
            }
        })

        if (isOnline()) {
            webView.loadUrl(baseUrl)
        } else {
            showOffline()
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            allowFileAccess = true
            allowContentAccess = true
            loadWithOverviewMode = true
            useWideViewPort = true
            builtInZoomControls = true
            displayZoomControls = false
            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
            cacheMode = WebSettings.LOAD_DEFAULT
            databaseEnabled = true
            setSupportMultipleWindows(false)
            defaultTextEncodingName = "UTF-8"
        }

        // Bridge untuk offline.html
        webView.addJavascriptInterface(NativeBridge(), "NativeBridge")

        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                progressBar.visibility = View.GONE
                swipeRefresh.isRefreshing = false
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                if (request?.isForMainFrame == true) {
                    showOffline()
                }
            }

            override fun onReceivedHttpError(
                view: WebView?,
                request: WebResourceRequest?,
                errorResponse: WebResourceResponse?
            ) {
                if (request?.isForMainFrame == true && (errorResponse?.statusCode ?: 0) >= 400) {
                    showOffline()
                }
            }

            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?
            ): Boolean {
                val url = request?.url.toString()
                // Memastikan semua halaman dari domain zinkros.my.id tetap terbuka di dalam WebView
                return when {
                    url.contains(appHost) -> false
                    url.startsWith("http://") || url.startsWith("https://") -> {
                        startActivity(Intent(Intent.ACTION_VIEW, url.toUri()))
                        true
                    }
                    else -> false
                }
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                if (newProgress < 100) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }
        }

        webView.setOnKeyListener { _, keyCode, _ ->
            if (keyCode == KeyEvent.KEYCODE_BACK && webView.canGoBack()) {
                webView.goBack()
                true
            } else {
                false
            }
        }
    }

    private fun setupSwipeRefresh() {
        swipeRefresh.setColorSchemeColors(getColor(R.color.primary))
        swipeRefresh.setOnRefreshListener {
            if (isOnline()) {
                webView.reload()
            } else {
                swipeRefresh.isRefreshing = false
                showOffline()
            }
        }
        // SwipeRefreshLayout hanya trigger dari atas
        webView.setOnScrollChangeListener { _, _, scrollY, _, _ ->
            swipeRefresh.isEnabled = scrollY == 0
        }
    }

    private fun isOnline(): Boolean {
        val network = connectivityManager.activeNetwork ?: return false
        val caps = connectivityManager.getNetworkCapabilities(network) ?: return false
        return caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    private fun showOffline() {
        isOffline = true
        webView.loadUrl("file:///android_asset/offline.html")
    }

    private fun showMain() {
        if (isOffline) {
            isOffline = false
            webView.loadUrl(baseUrl)
        }
    }

    private fun registerNetworkCallback() {
        networkCallback = object : ConnectivityManager.NetworkCallback() {
            override fun onAvailable(network: Network) {
                runOnUiThread {
                    if (isOffline) {
                        showMain()
                    }
                }
            }

            override fun onLost(network: Network) {
                runOnUiThread {
                    showOffline()
                }
            }

            override fun onCapabilitiesChanged(
                network: Network,
                caps: NetworkCapabilities
            ) {
                if (caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)) {
                    runOnUiThread {
                        if (isOffline) showMain()
                    }
                }
            }
        }

        val request = NetworkRequest.Builder()
            .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
            .build()

        connectivityManager.registerNetworkCallback(request, networkCallback!!)
    }

    // onBackPressed tradisional tidak dipakai karena sudah deprecated di SDK 34

    override fun onDestroy() {
        super.onDestroy()
        networkCallback?.let {
            connectivityManager.unregisterNetworkCallback(it)
        }
        webView.destroy()
    }

    // Bridge untuk offline.html → Kotlin
    inner class NativeBridge {
        @JavascriptInterface
        @Suppress("unused")
        fun reload() {
            runOnUiThread {
                if (isOnline()) {
                    showMain()
                } else {
                    webView.loadUrl("file:///android_asset/offline.html")
                }
            }
        }

        @JavascriptInterface
        @Suppress("unused")
        fun isOnline(): Boolean {
            return this@MainActivity.isOnline()
        }
    }
}