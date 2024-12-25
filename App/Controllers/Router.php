<?php

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Config/autoload.php';

class Router {
    private $db;
    

    public function __construct($db) {
        $this->db = $db;
    }

    public function handleRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
    
        switch ($uri) {
            case '/':
                $controller = new HomeController($this->db);
                $controller->index();
                break;
    
            case '/auth/login':
                $controller = new AuthController($this->db);
                $controller->login();
                break;
    
            case '/auth/register':
                $controller = new AuthController($this->db);
                $controller->register();
                break;
    
            case '/projects':
                $controller = new ProjectController($this->db);
                if ($method === 'GET') {
                    $controller->list();
                } else {
                    // Gérer les autres méthodes si nécessaire
                }
                break;
    
            case '/donations':
                $controller = new DonationController($this->db);
                if ($method === 'POST') {
                    // Ajouter un don
                    $amount = $_POST['amount'];
                    $projectId = $_POST['project_id'];
                    $userId = $_POST['user_id']; // Assurez-vous que l'ID de l'utilisateur est disponible
                    $controller->addDonation($amount, $projectId, $userId);
                } else {
                    // Récupérer les dons pour un projet
                    $projectId = $_GET['project_id'];
                    $donations = $controller->getDonationsByProject($projectId);
                    // Afficher les dons (vous pouvez appeler une vue ici)
                }
                break;
    
            default:
                http_response_code(404);
                echo "404 - Page not found.";
                break;
        }
    }
}