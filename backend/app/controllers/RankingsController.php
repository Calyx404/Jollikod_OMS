<?php

class RankingsController extends BaseController
{
    private MenuItem $menuItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuItemModel = new MenuItem();
    }

    // GET /api/branch/rankings/items?from=YYYY-MM-DD&to=YYYY-MM-DD&limit=10
    public function items(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));
        $limit = (int)$req->query('limit', 10);

        $rankings = $this->menuItemModel->getRankings($_SESSION['branch_id'], $from, $to, $limit);
        return $res->json(['status' => 200, 'rankings' => $rankings]);
    }
}
