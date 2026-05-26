import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.util.in);
        boolean running = true;

        while (running) {
            // Clear screen effect
            System.out.print("\033[H\033[2J");  
            System.flush();

            // 🌟 BEAUTIFUL HOME PAGE INTERFACE
            System.out.println("====================================================");
            System.out.println("    📚  SCHOOL LIBRARY MANAGEMENT SYSTEM  📚        ");
            System.out.println("====================================================");
            System.out.println("    Welcome back! Please select an option:          ");
            System.out.println("----------------------------------------------------");
            System.out.println("  [1] 📖 Book Management (Add, View, Search)       ");
            System.out.println("  [2] 👥 Student Directory (Register, View Profiles)");
            System.out.println("  [3] 🔄 Loan Logistics (Borrow/Return Books)       ");
            System.out.println("  [4] ⚙️ System Settings                           ");
            System.out.println("  [5] ❌ Exit Application                          ");
            System.out.println("----------------------------------------------------");
            System.out.print("✍️ Enter your choice (1-5): ");

            int choice = scanner.nextInt();

            switch (choice) {
                case 1:
                    System.out.println("\n📂 Opening Book Management...");
                    // Call your book management method here
                    waitForKey(scanner);
                    break;
                case 2:
                    System.out.println("\n📂 Opening Student Directory...");
                    // Call your student management method here
                    waitForKey(scanner);
                    break;
                case 3:
                    System.out.println("\n📂 Opening Loan Logistics...");
                    // Call your loan management method here
                    waitForKey(scanner);
                    break;
                case 4:
                    System.out.println("\n📂 Opening System Settings...");
                    waitForKey(scanner);
                    break;
                case 5:
                    System.out.println("\n👋 Thank you for using School Library System. Goodbye!");
                    running = false;
                    break;
                default:
                    System.out.println("\n⚠️ Invalid choice! Please enter a number between 1 and 5.");
                    waitForKey(scanner);
            }
        }
        scanner.close();
    }

    // Helper method to pause before returning to home page
    private static void key(Scanner scanner) {
        System.out.print("\nPress Enter to return to the Main Menu...");
        scanner.nextLine(); // catch leftover newline
        scanner.nextLine(); // wait for actual enter press
    }
}
