<?php

class Book {
    private string $title;
    private string $author;
    private string $isbn;
    private bool $available = true;
    private string $type; // 'physical' ou 'ebook'
    private array $reservations = []; // Gère les réservations pour les livres physiques

    public function __construct(string $title, string $author, string $isbn, string $type) {
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->type = $type;
    }

    public function isAvailable(): bool {
        return $this->available;
    }

    public function markAsAvailable() {
        $this->available = true;
    }

    public function markAsUnavailable() {
        $this->available = false;
    }

    public function reserve(string $user) {
        if ($this->type !== 'physical') {
            throw new Exception("Seuls les livres physiques peuvent être réservés.");
        }
        $this->reservations[] = $user;
    }

    public function getReservations(): array {
        return $this->reservations;
    }
}

class LoanManager {
    private array $loans = [];
    private array $notifications = [];

    public function borrowBook(Book $book, string $user) {
        if ($book->isAvailable()) {
            $book->markAsUnavailable();
            $this->loans[$user][] = $book;
            $this->sendNotification("Le livre '{$book->getTitle()}' a été emprunté par $user.");
        } else {
            throw new Exception("Le livre n'est pas disponible.");
        }
    }

    public function returnBook(Book $book, string $user) {
        if (isset($this->loans[$user])) {
            foreach ($this->loans[$user] as $index => $loanedBook) {
                if ($loanedBook === $book) {
                    $book->markAsAvailable();
                    unset($this->loans[$user][$index]);
                    $this->sendNotification("Le livre '{$book->getTitle()}' a été retourné par $user.");
                    break;
                }
            }
        } else {
            throw new Exception("Aucun prêt trouvé pour l'utilisateur $user.");
        }
    }

    private function sendNotification(string $message) {
        $this->notifications[] = $message;
        // Ici, on enverrait l'email ou le SMS, mais pour simplifier, on stocke les notifications
    }

    public function getNotifications(): array {
        return $this->notifications;
    }

    public function calculatePenalty(Book $book, DateTime $borrowedDate, DateTime $returnDate): float {
        $ratePerDay = 0.5; // Tarif de pénalité par jour de retard
        $daysLate = $borrowedDate->diff($returnDate)->days;
        return $daysLate * $ratePerDay;
    }
}
