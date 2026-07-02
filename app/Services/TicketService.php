<?php

require_once __DIR__ . '/../Core/Service.php';
require_once __DIR__ . '/../Repositories/FicheRepository.php';
require_once __DIR__ . '/../Helpers/audit.php';

class TicketService extends Service
{
    private FicheRepository $fiches;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->fiches = new FicheRepository($pdo);
    }

    public function loadPrintableFiche(int $ficheId): array
    {
        if ($ficheId <= 0) {
            http_response_code(400);
            die('Fiche invalide.');
        }

        $fiche = $this->fiches->findForTicket($ficheId);
        if (!$this->fiches->canAccess($fiche)) {
            audit_log($this->pdo, current_user_id(), 'PRINT_TICKET_DENIED', 'Tentative impression fiche ID ' . $ficheId . ' refusée.');
            http_response_code(404);
            die('Fiche introuvable.');
        }

        $fiche = $this->fiches->backfillTenantIfMissing($fiche);
        $details = $this->fiches->details($ficheId);
        audit_log($this->pdo, current_user_id(), 'PRINT_TICKET', 'Ticket imprimé/reprint: ' . ($fiche['fiche_code'] ?? ('ID ' . $ficheId)));

        return [$fiche, $details];
    }
}
