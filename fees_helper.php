<?php
/*
 * Fee calculations shared by the student pages.
 * Only payments with status 'Approved' count as paid. Student-submitted
 * payments stay 'Pending' until an admin verifies them in pages/fees.php.
 */

const FEE_MIN_INSTALLMENT = 5000;
const PAYMENT_MODES = ["UPI", "Cash", "Bank Transfer"];

function student_fee_summary(mysqli $conn, int $student_id): array
{
    $total = 0.0;
    $stmt = $conn->prepare("SELECT total_fees FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    $total = (float)$total;

    $paid = 0.0;
    $pending = 0.0;
    $stmt = $conn->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN status = 'Approved' THEN amount END), 0),
            COALESCE(SUM(CASE WHEN status = 'Pending'  THEN amount END), 0)
         FROM fees WHERE student_id = ?"
    );
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($paid, $pending);
    $stmt->fetch();
    $stmt->close();

    $paid = (float)$paid;
    $pending = (float)$pending;

    return [
        "total"     => $total,
        "paid"      => $paid,
        "pending"   => $pending,
        "remaining" => max(0, $total - $paid),
        "payable"   => max(0, $total - $paid - $pending),
    ];
}

function fee_min_amount(float $payable): float
{
    return min((float)FEE_MIN_INSTALLMENT, $payable);
}
