<?php
abstract class PaymentMethod {
    protected $amount;
    protected $orderId;
    protected $connection;
    protected $db;

    public function __construct($amount, $orderId, PDO $connection) {
        $this->amount = $amount;
        $this->orderId = $orderId;
        $this->connection = $connection;
        $this->db = $connection;
    }

    public function getActivePaymentMethods() {
        $stmt = $this->db->prepare("SELECT id, name FROM payment_methods");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    abstract public function processPayment(): bool;
    abstract public function validatePayment(): bool;

    public function updateOrderPaymentStatus($orderId, $status) {
        $stmt = $this->connection->prepare("
            UPDATE orders 
            SET payment_status = :status 
            WHERE id = :orderId
        ");
        return $stmt->execute([
            ':status' => $status,
            ':orderId' => $orderId
        ]);
    }
}

class CreditCardPayment extends PaymentMethod {
    private $cardNumber;
    private $expiryDate;
    private $cvv;
    
    public function __construct($amount, $orderId, PDO $connection, $cardNumber, $expiryDate, $cvv) {
        parent::__construct($amount, $orderId, $connection);
        $this->cardNumber = $cardNumber;
        $this->expiryDate = $expiryDate;
        $this->cvv = $cvv;
    }

    public function validatePayment(): bool {
        if (empty($this->cardNumber) || strlen($this->cardNumber) !== 16) {
            throw new Exception("Invalid card number");
        } 
        if (empty($this->expiryDate) || !preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $this->expiryDate)) {
            throw new Exception("Invalid expiry date");
        }
        if (empty($this->cvv) || !preg_match('/^[0-9]{3,4}$/', $this->cvv)) {
            throw new Exception("Invalid CVV");
        }

        return true;
    }

    public function processPayment(): bool {
        try {
            if (!$this->validatePayment()) {
                return false;
            }

            $stmt = $this->connection->prepare("UPDATE orders SET payment_status = 'paid', payment_date = NOW() WHERE id = ?");
            $stmt->execute([$this->orderId]);
            
            return true;

        } catch (Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return false;
        }
    }
}

class CashOnDeliveryPayment extends PaymentMethod {
    public function validatePayment(): bool {
        return $this->amount > 0 && $this->orderId > 0;
    }
    public function processPayment(): bool {
        try {
            if (!$this->validatePayment()) {
                return false;
            }

            $stmt = $this->connection->prepare("UPDATE orders SET payment_status = 'pending',payment_method = 'cash_on_delivery' WHERE id = ?");
            $stmt->execute([$this->orderId]);
            return true;
        } 
        catch (Exception $e) {
            error_log("COD processing error: " . $e->getMessage());
            return false;
        }
    }
}
?>