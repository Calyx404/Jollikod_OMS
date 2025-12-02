<?php

class MenuController extends BaseController
{
    private Menu $menuModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuModel = new Menu();
    }

    // POST /api/menu/create
    public function create(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $menu_id = $this->menuModel->createMenu($branch_id);

        return $res->json([
            'status' => 201,
            'message' => 'Menu created successfully',
            'menu_id' => $menu_id
        ]);
    }

    // GET /api/menu/list
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $menus = $this->menuModel->getMenusByBranch($branch_id);

        return $res->json([
            'status' => 200,
            'menus' => $menus
        ]);
    }
}
