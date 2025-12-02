<?php

class CustomerAnalyticsController extends BaseController
{
    private Customer $customerModel;
    private OrderItem $orderItemModel;
    private Payment $paymentModel;
    private Feedback $feedbackModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->orderItemModel = new OrderItem();
        $this->paymentModel = new Payment();
        $this->feedbackModel = new Feedback();
    }

    // GET /api/branch/analytics/customer-demographics?from=2025-12-01&to=2025-12-02
    public function customerDemographics(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $customers = $this->customerModel->getCustomerDemographics($branch_id, $from, $to);

        return $res->json([
            'status' => 200,
            'customers' => $customers
        ]);
    }

    // GET /api/branch/analytics/feedback-summary?from=2025-12-01&to=2025-12-02
    public function feedbackSummary(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $feedbackStats = $this->feedbackModel->getFeedbackStats($branch_id, $from, $to);

        return $res->json([
            'status' => 200,
            'total_feedbacks' => $feedbackStats['total_feedbacks'],
            'average_rating' => $feedbackStats['average_rating'] ?? 0
        ]);
    }
}
