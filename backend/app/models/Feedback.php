<?php

class Feedback extends BaseModel {
    
    protected string $table = 'feedbacks';

    public function getFeedbackStats(int $branch_id, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(feedback_id) AS total_feedbacks,
                AVG(rating) AS average_rating
            FROM {$this->table}
            WHERE branch_id = :branch_id
            AND DATE(created_at) BETWEEN :from AND :to
        ");
        $stmt->execute([
            ':branch_id' => $branch_id,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}