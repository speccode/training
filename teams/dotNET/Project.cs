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

    
}