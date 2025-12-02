<?php

class MenuItemScheduleController extends BaseController
{
    private MenuItemSchedule $scheduleModel;
    private MenuItem $menuItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->scheduleModel = new MenuItemSchedule();
        $this->menuItemModel = new MenuItem();
    }

    // POST /api/menu-item/schedule/create
    // payload: { menu_item_id, start_date (YYYY-MM-DD|null), end_date, days_of_week (e.g. "1,2,3"), start_time (HH:MM:SS), end_time }
    public function create(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['menu_item_id'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $menu_item_id = (int)$data['menu_item_id'];

        // Verify ownership (menu item belongs to branch)
        $items = $this->menuItemModel->getByBranch($_SESSION['branch_id']);
        $owns = false;
        foreach ($items as $it) {
            if ((int)$it['menu_item_id'] === $menu_item_id) { $owns = true; break; }
        }
        if (!$owns) {
            return $res->json(['status' => 403, 'message' => 'Menu item not found in your branch'], 403);
        }

        $payload = [
            'menu_item_id' => $menu_item_id,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'days_of_week' => isset($data['days_of_week']) ? $data['days_of_week'] : null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null
        ];

        $id = $this->scheduleModel->create($payload);

        return $res->json(['status' => 201, 'message' => 'Schedule created', 'schedule_id' => $id]);
    }

    // GET /api/menu-item/schedule/list?menu_item_id=#
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $menu_item_id = (int)$req->query('menu_item_id', 0);
        if ($menu_item_id <= 0) return $res->json(['status' => 400, 'message' => 'menu_item_id required'], 400);

        // verify ownership
        $items = $this->menuItemModel->getByBranch($_SESSION['branch_id']);
        $owns = false;
        foreach ($items as $it) {
            if ((int)$it['menu_item_id'] === $menu_item_id) { $owns = true; break; }
        }
        if (!$owns) return $res->json(['status' => 403, 'message' => 'Menu item not found in your branch'], 403);

        $rows = $this->scheduleModel->listByMenuItem($menu_item_id);
        return $res->json(['status' => 200, 'schedules' => $rows]);
    }

    // POST /api/menu-item/schedule/delete
    public function delete(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['schedule_id'], $data);
        if (!empty($errors)) return $res->json(['status' => 422, 'errors' => $errors], 422);

        $schedule_id = (int)$data['schedule_id'];

        // get schedule to check ownership
        $schedule = $this->scheduleModel->getById($schedule_id);
        if (!$schedule) return $res->json(['status' => 404, 'message' => 'Schedule not found'], 404);

        // verify that menu_item belongs to branch
        $menu_item_id = (int)$schedule['menu_item_id'];
        $items = $this->menuItemModel->getByBranch($_SESSION['branch_id']);
        $owns = false;
        foreach ($items as $it) {
            if ((int)$it['menu_item_id'] === $menu_item_id) { $owns = true; break; }
        }
        if (!$owns) return $res->json(['status' => 403, 'message' => 'Not authorized'], 403);

        $ok = $this->scheduleModel->delete($schedule_id);
        if (!$ok) return $res->json(['status' => 500, 'message' => 'Failed to delete'], 500);
        return $res->json(['status' => 200, 'message' => 'Deleted']);
    }
}
