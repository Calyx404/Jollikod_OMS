<?php

class MenuItemSchedule extends BaseModel
{
    protected string $table = 'menu_item_schedules';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (menu_item_id, start_date, end_date, days_of_week, start_time, end_time)
            VALUES (:menu_item_id, :start_date, :end_date, :days_of_week, :start_time, :end_time)
        ");
        $stmt->execute([
            ':menu_item_id' => $data['menu_item_id'],
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':days_of_week' => $data['days_of_week'] ?? null,
            ':start_time' => $data['start_time'] ?? null,
            ':end_time' => $data['end_time'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listByMenuItem(int $menu_item_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE menu_item_id = :id ORDER BY created_at DESC");
        $stmt->execute([':id' => $menu_item_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $schedule_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE schedule_id = :id");
        return $stmt->execute([':id' => $schedule_id]);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM menu_item_schedules 
            WHERE schedule_id = :id LIMIT 1
        ");
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find schedules for a menu_item that match a specific datetime.
     * $dt: DateTime object
     */
    public function schedulesForDatetime(int $menu_item_id, DateTime $dt): array
    {
        // We'll fetch candidate schedules for the item and filter in PHP.
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE menu_item_id = :id");
        $stmt->execute([':id' => $menu_item_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $matches = [];
        $dayIndex = (int)$dt->format('w'); // 0 (Sunday) through 6 (Saturday)
        $date = $dt->format('Y-m-d');
        $time = $dt->format('H:i:s');

        foreach ($rows as $r) {
            // check date range
            if ($r['start_date'] && $date < $r['start_date']) continue;
            if ($r['end_date'] && $date > $r['end_date']) continue;

            // check days of week
            if ($r['days_of_week']) {
                $days = array_map('intval', array_filter(array_map('trim', explode(',', $r['days_of_week']))));
                if (!in_array($dayIndex, $days, true)) continue;
            }

            // check time window
            if ($r['start_time'] && $r['end_time']) {
                // normal case
                if ($r['start_time'] <= $r['end_time']) {
                    if ($time < $r['start_time'] || $time > $r['end_time']) continue;
                } else {
                    // overnight window (e.g., 22:00 - 02:00)
                    if (!($time >= $r['start_time'] || $time <= $r['end_time'])) continue;
                }
            }
            // if start_time or end_time is NULL => treat as all-day for that schedule

            $matches[] = $r;
        }

        return $matches;
    }
}
