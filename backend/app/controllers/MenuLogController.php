<?php

class MenuLogController extends BaseController
{
    private MenuLog $menuLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuLogModel = new MenuLog();
    }

    // GET /api/branch/menu-logs?menu_id=1
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $menu_id = (int)$req->query('menu_id', 0);
        if ($menu_id <= 0) {
            return $res->json(['status' => 400, 'message' => 'menu_id required'], 400);
        }

        $logs = $this->menuLogModel->listByMenu($menu_id);
        return $res->json(['status' => 200, 'logs' => $logs]);
    }
}
