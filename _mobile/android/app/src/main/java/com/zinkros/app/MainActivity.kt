package com.zinkros.app

import android.annotation.SuppressLint
import android.content.ActivityNotFoundException
import android.content.Intent
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.NetworkRequest
import android.os.Bundle
import android.view.View
import android.webkit.*
import android.widget.ProgressBar
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.credentials.CreatePublicKeyCredentialRequest
import androidx.credentials.CreatePublicKeyCredentialResponse
import androidx.credentials.CredentialManager
import androidx.credentials.GetCredentialRequest
import androidx.credentials.GetPublicKeyCredentialOption
import androidx.credentials.PublicKeyCredential
import androidx.credentials.exceptions.CreateCredentialException
import androidx.credentials.exceptions.GetCredentialException
import androidx.lifecycle.lifecycleScope
import kotlinx.coroutines.launch
import org.json.JSONObject
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

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        connectivityManager = getSystemService(CONNECTIVITY_SERVICE) as ConnectivityManager
        webView = findViewById(R.id.webView)
        progressBar = findViewById(R.id.progressBar)
        swipeRefresh = findViewById(R.id.swipeRefresh)

        ViewCompat.setOnApplyWindowInsetsListener(swipeRefresh) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(0, systemBars.top, 0, systemBars.bottom)
            insets
        }

        setupWebView()
        setupSwipeRefresh()
        registerNetworkCallback()

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

        val data = intent?.data
        if (isOnline()) {
            if (data != null) {
                webView.loadUrl(data.toString())
            } else {
                webView.loadUrl(baseUrl)
            }
        } else {
            showOffline()
        }
    }

    override fun onNewIntent(intent: Intent?) {
        super.onNewIntent(intent)
        setIntent(intent)
        val data = intent?.data
        if (data != null && !isOffline) {
            webView.loadUrl(data.toString())
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            allowFileAccess = false
            allowContentAccess = true
            loadWithOverviewMode = true
            useWideViewPort = true
            builtInZoomControls = true
            displayZoomControls = false
            mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
            cacheMode = WebSettings.LOAD_DEFAULT
            databaseEnabled = true
            setSupportMultipleWindows(false)
            defaultTextEncodingName = "UTF-8"
        }

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
                val uri = request?.url ?: return false
                val host = uri.host

                return if (host != null && (host == appHost || host.endsWith(".$appHost"))) {
                    false
                } else {
                    try {
                        val customTabsIntent = androidx.browser.customtabs.CustomTabsIntent.Builder().build()
                        customTabsIntent.launchUrl(this@MainActivity, uri)
                    } catch (e: Exception) {
                        try {
                            startActivity(Intent(Intent.ACTION_VIEW, uri))
                        } catch (_: ActivityNotFoundException) {
                            Toast.makeText(this@MainActivity, "Tidak dapat membuka tautan", Toast.LENGTH_SHORT).show()
                        }
                    }
                    true
                }
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                if (newProgress < 100) {
                    progressBar.visibility = View.VISIBLE
                    progressBar.progress = newProgress
                } else {
                    progressBar.visibility = View.GONE
                }
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
                    if (isOffline) showMain()
                }
            }

            override fun onLost(network: Network) {
                runOnUiThread {
                    showOffline()
                }
            }
        }

        val request = NetworkRequest.Builder()
            .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
            .build()

        connectivityManager.registerNetworkCallback(request, networkCallback!!)
    }

    override fun onDestroy() {
        super.onDestroy()
        networkCallback?.let {
            connectivityManager.unregisterNetworkCallback(it)
        }
        webView.destroy()
    }

    inner class NativeBridge {
        private val credentialManager: CredentialManager by lazy {
            CredentialManager.create(this@MainActivity)
        }

        @JavascriptInterface
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
        fun isOnline(): Boolean {
            return this@MainActivity.isOnline()
        }

        /**
         * Sign in with an existing passkey.
         * JS: NativeBridge.passkeyGet(id, requestJson)
         * requestJson = PublicKeyCredentialRequestOptions JSON (WebAuthn web JSON).
         * Result is delivered asynchronously to window.__nativePasskeyCallback.
         */
        @JavascriptInterface
        fun passkeyGet(id: String, requestJson: String) {
            lifecycleScope.launch {
                try {
                    val option = GetPublicKeyCredentialOption(requestJson)
                    val request = GetCredentialRequest.Builder()
                        .addCredentialOption(option)
                        .build()
                    val response = credentialManager.getCredential(this@MainActivity, request)
                    val credential = response.credential
                    if (credential is PublicKeyCredential) {
                        sendPasskeyResult(id, true, credential.authenticationResponseJson)
                    } else {
                        sendPasskeyResult(id, false, "Tipe kredensial tidak didukung: ${credential.type}")
                    }
                } catch (e: GetCredentialException) {
                    sendPasskeyResult(id, false, friendlyPasskeyError(e.type, e.message))
                } catch (e: Exception) {
                    sendPasskeyResult(id, false, e.message ?: "Terjadi kesalahan")
                }
            }
        }

        /**
         * Register a new passkey.
         * JS: NativeBridge.passkeyCreate(id, requestJson)
         * requestJson = PublicKeyCredentialCreationOptions JSON (WebAuthn web JSON).
         * Result is delivered asynchronously to window.__nativePasskeyCallback.
         */
        @JavascriptInterface
        fun passkeyCreate(id: String, requestJson: String) {
            lifecycleScope.launch {
                try {
                    val request = CreatePublicKeyCredentialRequest(requestJson)
                    val response = credentialManager.createCredential(this@MainActivity, request)
                    if (response is CreatePublicKeyCredentialResponse) {
                        sendPasskeyResult(id, true, response.registrationResponseJson)
                    } else {
                        sendPasskeyResult(id, false, "Tipe kredensial tidak didukung: ${response.type}")
                    }
                } catch (e: CreateCredentialException) {
                    sendPasskeyResult(id, false, friendlyPasskeyError(e.type, e.message))
                } catch (e: Exception) {
                    sendPasskeyResult(id, false, e.message ?: "Terjadi kesalahan")
                }
            }
        }

        /**
         * Deliver a passkey result back to the page.
         * Envelope: { id, ok: Boolean, data: Object } on success, { id, ok: false, error: String } on failure.
         */
        private fun sendPasskeyResult(id: String, ok: Boolean, payload: String) {
            runOnUiThread {
                val envelope = JSONObject()
                envelope.put("id", id)
                envelope.put("ok", ok)
                if (ok) {
                    envelope.put("data", JSONObject(payload))
                } else {
                    envelope.put("error", payload)
                }
                val script = "window.__nativePasskeyCallback && window.__nativePasskeyCallback($envelope)"
                webView.evaluateJavascript(script, null)
            }
        }

        private fun friendlyPasskeyError(type: String?, message: String?): String {
            val m = (message ?: "").lowercase()
            val t = (type ?: "").lowercase()
            return when {
                t.contains("cancel") || m.contains("cancel") -> "Dibatalkan oleh pengguna"
                t.contains("nocredential") -> "Tidak ada passkey tersimpan untuk akun ini"
                t.contains("interrupt") -> "Proses passkey terputus, coba lagi"
                !message.isNullOrBlank() -> message
                type != null -> type
                else -> "Terjadi kesalahan"
            }
        }
    }
}
