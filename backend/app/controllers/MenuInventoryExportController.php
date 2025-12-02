<?php

class MenuInventoryExportController extends BaseController
{
    private MenuInventory $inventoryModel;
    private MenuLog $menuLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->inventoryModel = new MenuInventory();
        $this->menuLogModel = new MenuLog();
    }

    // GET /api/branch/analytics/inventory-log/export?from=2025-12-01&to=2025-12-02
    public function exportInventoryLog(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $logs = $this->menuLogModel->getLogsByBranch($branch_id, $from, $to);

        CsvExporter::export('inventory_log.csv', $logs);
    }

    // GET /api/branch/analytics/inventory-status
    public function inventoryStatus(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];

        $inventorySummary = $this->inventoryModel->getInventorySummaryByBranch($branch_id);

        return $res->json([
            'status' => 200,
            'inventory' => $inventorySummary
        ]);
    }
}
