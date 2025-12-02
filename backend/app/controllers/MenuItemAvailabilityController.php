<?php

class MenuItemAvailabilityController extends BaseController
{
    private MenuItem $menuItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuItemModel = new MenuItem();
    }

    // POST /api/menu-item/toggle
    // payload: { menu_item_id, is_active }  (is_active = 0 or 1)
    public function toggle(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['menu_item_id', 'is_active'], $data);
        if (!empty($errors)) return $res->json(['status' => 422, 'errors' => $errors], 422);

        $menu_item_id = (int)$data['menu_item_id'];
        $isActive = (int)$data['is_active'] === 1;

        // ensure ownership
        $items = $this->menuItemModel->getByBranch($_SESSION['branch_id']);
        $owns = false;
        foreach ($items as $it) {
            if ((int)$it['menu_item_id'] === $menu_item_id) { $owns = true; break; }
        }
        if (!$owns) return $res->json(['status' => 403, 'message' => 'Menu item not found in your branch'], 403);

        $ok = $this->menuItemModel->setActive($menu_item_id, $isActive);
        if (!$ok) return $res->json(['status' => 500, 'message' => 'Failed to update active flag'], 500);

        return $res->json(['status' => 200, 'message' => 'Updated', 'is_active' => $isActive ? 1 : 0]);
    }
}
