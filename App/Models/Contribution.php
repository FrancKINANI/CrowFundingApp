<?php

class Contribution {
    private $id;
    private $projectId;
    private $userId;
    private $amount;
    private static $file = __DIR__ . '../Data/contributions.json';

    public function __construct($id = 0, $projectId = 0, $userId = 0, $amount = 0) {
        $this->id = $id;
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->amount = $amount;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'projectId' => $this->projectId,
            'userId' => $this->userId,
            'amount' => $this->amount
        ];
    }

    public function save() {
        $contributions = self::all();
        $contributions[] = $this->toArray();
        file_put_contents(self::$file, json_encode($contributions, JSON_PRETTY_PRINT));
    }

    public function setAmount($amount){
        $this->amount = $amount;
    }

    public static function find($id){
        $contributions = self::all();
        foreach ($contributions as $contribution) {
            if ($contribution['id'] == $id) {
                return new Contribution($contribution['id'], $contribution['projectId'], 
                $contribution['userId'], $contribution['amount']);
            }
        }
    }

    public static function all() {
        return file_exists(self::$file) ? json_decode(file_get_contents(self::$file), true) : [];
    }
}
