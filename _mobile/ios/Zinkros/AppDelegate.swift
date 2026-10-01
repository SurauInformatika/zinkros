import UIKit

@main
class AppDelegate: UIResponder, UIApplicationDelegate {

    var window: UIWindow?

    func application(
        _ application: UIApplication,
        didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
    ) -> Bool {
        window = UIWindow(frame: UIScreen.main.bounds)
        let viewController = ViewController()
        let nav = UINavigationController(rootViewController: viewController)
        nav.setNavigationBarHidden(true, animated: false)
        window?.rootViewController = nav
        window?.makeKeyAndVisible()

        // Status bar style
        UIApplication.shared.statusBarStyle = .lightContent

        return true
    }

    func applicationWillTerminate(_ application: UIApplication) {
        // Cleanup
    }
}