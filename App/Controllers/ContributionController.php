<?php

// require_once __DIR__ . '/Contribution.php';

class ContributionController {
    private $contributionModel;
    private $projectModel;
    public function __construct($contributionModel, $projectModel){
        $this->contributionModel = $contributionModel;
        $this->projectModel = $projectModel;
    }
    public function create($projectId, $userId, $amount) {
        $contributions = Contribution::all();
        $id = count($contributions) + 1;

        $contribution = new Contribution($id, $projectId, $userId, $amount);
        $contribution->save();
        echo "Contribution added successfully!";
    }

    public function list() {
        return Contribution::all();
    }

    public function delete($id) {
        FileManager::delete(__DIR__ . '/../data/contributions.json', $id);
        echo "Contribution deleted successfully!";
    }

    public function edit($id, $amount){
        $contribution = Contribution::find($id);
        $contribution->setAmount($amount);
        $contribution->save();
        echo "Contribution updated successfully!";
    }
}
