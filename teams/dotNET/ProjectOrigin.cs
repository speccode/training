using System;
using System.Data.SqlClient;

namespace ComplexLegacyController
{
    class Program
    {
        static void Main(string[] args)
        {
            // DB Connection is passed here (hardcoded string for simplicity).
            var dbConnection = new SqlConnection("Server=myServerAddress;Database=myDB;User Id=myUsername;Password=myPassword;");

            var controller = new OrderController(dbConnection);
            controller.ProcessOrder(12, "EXPRESS", 500, true, DateTime.Now);
        }
    }

    public class OrderController
    {
        private SqlConnection _dbConnection;

        public OrderController(SqlConnection dbConnection)
        {
            _dbConnection = dbConnection;
        }

        // ProcessOrder has all workflows
        public void ProcessOrder(int orderId, string shippingType, decimal amount, bool applyDiscount, DateTime orderDate)
        {
            // Initializations of primitive obsessed parameters
            string orderStatus = "PENDING";
            bool isExpressShipping = false; // Just to keep track for later
            
            // Workflow 1: Check for order validity with some complex rules
            if (orderId < 0 || amount <= 0 || string.IsNullOrEmpty(shippingType))
            {
                // Invalid input, halt processing
                throw new ArgumentException("Invalid order parameters!");
            }
            
            // Workflow 2: Handle Shipping Logic
            if (shippingType == "EXPRESS")
            {
                isExpressShipping = true;
                orderStatus = "PRIORITY"; // Express shipping means priority order
            }
            else if (shippingType == "STANDARD")
            {
                orderStatus = "NORMAL"; // Standard shipping means normal order
            }
            else if (shippingType == "INTERNATIONAL" && amount > 1000)
            {
                orderStatus = "INTERNATIONAL_HIGH_VALUE"; // TODO: Needs confirmation from business
            }
            else if (shippingType == "SAME_DAY" && DateTime.Now.Hour >= 9 && DateTime.Now.Hour <= 16)
            {
                // TODO: Someone said Same Day shipping works between 9 AM - 4 PM, but no one documented it
                orderStatus = "URGENT"; // Same Day orders should be marked urgent
            }
            else
            {
                orderStatus = "UNKNOWN_SHIPPING";
                // TODO: Ask business how to handle undefined shipping types.
            }

            // Workflow 3: Check customer’s discount eligibility
            bool customerEligibleForDiscount = false;
            if (applyDiscount && amount > 100)
            {
                customerEligibleForDiscount = true;
            }
            else if (applyDiscount && orderDate.DayOfWeek == DayOfWeek.Monday)
            {
                customerEligibleForDiscount = true; // Monday Discount: Random decision made during a meeting
            }

            // Workflow 4: Apply any conditional fees
            decimal shippingFee = 0;
            if (isExpressShipping && amount < 100)
            {
                shippingFee = 50; // Express fee for orders less than $100
            }
            else if (amount > 500 && shippingType == "INTERNATIONAL")
            {
                shippingFee = 100; // High shipping fee for international expensive orders
            }

            // Workflow 5: Interact with Database for Inventory and Customer Data
            SqlCommand getCustomerCmd = new SqlCommand($"SELECT LoyaltyLevel FROM Customers WHERE OrderId = {orderId}", _dbConnection);
            _dbConnection.Open();
            var loyaltyLevel = getCustomerCmd.ExecuteScalar();

            if (loyaltyLevel != null && loyaltyLevel.ToString() == "GOLD")
            {
                // Gold customers get free shipping if total exceeds $300
                if (amount > 300)
                {
                    shippingFee = 0;
                }
            }

            // More complex inventory validation inside the controller
            SqlCommand inventoryCheckCmd = new SqlCommand($"SELECT Stock FROM Inventory WHERE OrderId = {orderId}", _dbConnection);
            var stock = inventoryCheckCmd.ExecuteScalar();
            if (stock != null && Convert.ToInt32(stock) > 0)
            {
                // Enough stock
                orderStatus = "READY_TO_SHIP";
            }
            else
            {
                // Stock is low or out, mark backorder
                orderStatus = "BACKORDER";
            }

            // SQL to update the final order status
            SqlCommand updateOrderCmd = new SqlCommand($"UPDATE Orders SET Status = '{orderStatus}', ShippingFee = {shippingFee} WHERE Id = {orderId}", _dbConnection);
            var rowsAffected = updateOrderCmd.ExecuteNonQuery();

            if (rowsAffected == 0)
            {
                throw new Exception("Order update failed, no rows affected.");
            }

            _dbConnection.Close();

            // Final processing - sending email notifications, TODO later
            // TODO: Implement email notifications for "EXPRESS" and "URGENT" orders
            if (orderStatus == "URGENT" || isExpressShipping)
            {
                Console.WriteLine("Email notification sent to the customer for high-priority shipping.");
            }

            // TODO: Think about refactoring this mess
            Console.WriteLine($"Order {orderId} processed with status {orderStatus} and shipping fee {shippingFee}");
        }
    }
}