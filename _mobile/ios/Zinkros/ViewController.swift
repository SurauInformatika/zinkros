import UIKit
import WebKit
import SystemConfiguration

class ViewController: UIViewController, WKNavigationDelegate, WKScriptMessageHandler {

    private var webView: WKWebView!
    private var progressView: UIProgressView!
    private var splashView: UIView!
    private var isOffline = false

    private let baseUrl = "https://zinkros.my.id/"
    private let primary = UIColor(red: 5/255, green: 150/255, blue: 105/255, alpha: 1)
    private var reachability: SCNetworkReachability?

    override func viewDidLoad() {
        super.viewDidLoad()
        setupSplash()
        setupWebView()
        setupProgressBar()
        setupReachability()

        // Tampilkan splash selama 2 detik
        DispatchQueue.main.asyncAfter(deadline: .now() + 2.0) {
            self.hideSplash()
        }
    }

    // MARK: - Splash Screen

    private func setupSplash() {
        // Full screen overlay
        splashView = UIView(frame: view.bounds)
        splashView.backgroundColor = primary
        splashView.autoresizingMask = [.flexibleWidth, .flexibleHeight]

        // Logo
        let logoImageView = UIImageView()
        logoImageView.image = UIImage(named: "splash_logo")
        logoImageView.contentMode = .scaleAspectFit
        logoImageView.translatesAutoresizingMaskIntoConstraints = false
        splashView.addSubview(logoImageView)

        // App Name
        let titleLabel = UILabel()
        titleLabel.text = "Zinkros"
        titleLabel.textColor = .white
        titleLabel.font = UIFont.systemFont(ofSize: 20, weight: .semibold)
        titleLabel.textAlignment = .center
        titleLabel.translatesAutoresizingMaskIntoConstraints = false
        splashView.addSubview(titleLabel)

        // Version
        let versionLabel = UILabel()
        versionLabel.text = "v1.0"
        versionLabel.textColor = UIColor.white.withAlphaComponent(0.5)
        versionLabel.font = UIFont.systemFont(ofSize: 12)
        versionLabel.textAlignment = .center
        versionLabel.translatesAutoresizingMaskIntoConstraints = false
        splashView.addSubview(versionLabel)

        NSLayoutConstraint.activate([
            logoImageView.centerXAnchor.constraint(equalTo: splashView.centerXAnchor),
            logoImageView.centerYAnchor.constraint(equalTo: splashView.centerYAnchor, constant: -30),
            logoImageView.widthAnchor.constraint(equalToConstant: 120),
            logoImageView.heightAnchor.constraint(equalToConstant: 120),

            titleLabel.topAnchor.constraint(equalTo: logoImageView.bottomAnchor, constant: 16),
            titleLabel.centerXAnchor.constraint(equalTo: splashView.centerXAnchor),

            versionLabel.bottomAnchor.constraint(equalTo: splashView.bottomAnchor, constant: -40),
            versionLabel.centerXAnchor.constraint(equalTo: splashView.centerXAnchor)
        ])

        view.addSubview(splashView)
    }

    private func hideSplash() {
        UIView.animate(withDuration: 0.5, animations: {
            self.splashView.alpha = 0
        }) { _ in
            self.splashView.removeFromSuperview()
            if self.isOnline() {
                self.loadMain()
            } else {
                self.showOffline()
            }
        }
    }

    // MARK: - WebView

    private func setupWebView() {
        let config = WKWebViewConfiguration()
        config.allowsInlineMediaPlayback = true
        config.mediaTypesRequiringUserActionForPlayback = []
        config.userContentController.add(self, name: "NativeBridge")

        webView = WKWebView(frame: .zero, configuration: config)
        webView.navigationDelegate = self
        webView.scrollView.bounces = true
        webView.allowsBackForwardNavigationGestures = true
        webView.isOpaque = false
        webView.backgroundColor = .white

        view.addSubview(webView)
        webView.translatesAutoresizingMaskIntoConstraints = false
        NSLayoutConstraint.activate([
            webView.topAnchor.constraint(equalTo: view.safeAreaLayoutGuide.topAnchor),
            webView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            webView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            webView.bottomAnchor.constraint(equalTo: view.bottomAnchor)
        ])
    }

    private func setupProgressBar() {
        progressView = UIProgressView(progressViewStyle: .default)
        progressView.trackTintColor = .clear
        progressView.progressTintColor = primary
        progressView.isHidden = true

        view.addSubview(progressView)
        progressView.translatesAutoresizingMaskIntoConstraints = false
        NSLayoutConstraint.activate([
            progressView.topAnchor.constraint(equalTo: view.safeAreaLayoutGuide.topAnchor),
            progressView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            progressView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            progressView.heightAnchor.constraint(equalToConstant: 2)
        ])

        webView.addObserver(self, forKeyPath: "estimatedProgress", options: .new, context: nil)
    }

    private func setupReachability() {
        let host = baseUrl.replacingOccurrences(of: "http://", with: "")
            .replacingOccurrences(of: "https://", with: "")
            .components(separatedBy: "/").first ?? ""
        reachability = SCNetworkReachabilityCreateWithName(nil, host)
    }

    private func isOnline() -> Bool {
        var flags = SCNetworkReachabilityFlags()
        guard let reachability = reachability, SCNetworkReachabilityGetFlags(reachability, &flags) else {
            return false
        }
        return flags.contains(.reachable) && !flags.contains(.connectionRequired)
    }

    private func loadMain() {
        isOffline = false
        if let url = URL(string: baseUrl) {
            webView.load(URLRequest(url: url))
        }
    }

    private func showOffline() {
        isOffline = true
        if let url = URL(string: "offline.html") {
            webView.load(URLRequest(url: url))
        }
    }

    // MARK: - WKNavigationDelegate

    func webView(_ webView: WKWebView, didStartProvisionalNavigation navigation: WKNavigation!) {
        progressView.isHidden = false
    }

    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        progressView.isHidden = true
        progressView.progress = 0
    }

    func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) {
        showOffline()
    }

    func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
        showOffline()
    }

    func webView(_ webView: WKWebView, decidePolicyFor navigationAction: WKNavigationAction, decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
        guard let url = navigationAction.request.url else {
            decisionHandler(.cancel)
            return
        }

        let urlString = url.absoluteString
        if urlString.contains("zinkros.my.id") {
            decisionHandler(.allow)
        } else if urlString.hasPrefix("http://") || urlString.hasPrefix("https://") {
            UIApplication.shared.open(url)
            decisionHandler(.cancel)
        } else {
            decisionHandler(.allow)
        }
    }

    // MARK: - WKScriptMessageHandler

    func userContentController(_ userContentController: WKUserContentController, didReceive message: WKScriptMessage) {
        if message.name == "NativeBridge" {
            if let body = message.body as? [String: Any],
               let action = body["action"] as? String {
                switch action {
                case "reload":
                    if isOnline() { loadMain() } else { showOffline() }
                case "isOnline":
                    let js = "window.NativeBridgeCallback(\(isOnline()))"
                    webView.evaluateJavaScript(js)
                default:
                    break
                }
            }
        }
    }

    // MARK: - KVO

    override func observeValue(forKeyPath keyPath: String?, of object: Any?, change: [NSKeyValueChangeKey: Any]?, context: UnsafeMutableRawPointer?) {
        if keyPath == "estimatedProgress" {
            progressView.progress = Float(webView.estimatedProgress)
        }
    }

    deinit {
        webView.removeObserver(self, forKeyPath: "estimatedProgress")
    }
}