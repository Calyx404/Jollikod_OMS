<?php

class BranchAnalyticsExportController extends BaseController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private Customer $customerModel;
    private Payment $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->customerModel = new Customer();
        $this->paymentModel = new Payment();
    }

    // GET /api/branch/analytics/sales-log/export?from=2025-12-01&to=2025-12-02
    public function exportSalesLog(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $salesLog = $this->orderItemModel->getSalesLog($branch_id, $from, $to);

        CsvExporter::export('sales_log.csv', $salesLog);
    }

    // GET /api/branch/analytics/customer-log/export?from=2025-12-01&to=2025-12-02
    public function exportCustomerLog(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $customerLog = $this->customerModel->getCustomerDemographics($branch_id, $from, $to);

        CsvExporter::export('customer_log.csv', $customerLog);
    }
}
