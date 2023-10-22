<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\App\Enum\YesNoStatus;
use Platine\App\Filter\UserFilter;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\App\Model\Repository\DemoRepository;
use Platine\App\Module\Etl\Enum\DataDefinitionDirection;
use Platine\App\Module\Etl\Enum\DataDefinitionImportStatus;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Lang\Lang;

/**
 * @class StatusList
 * @package Platine\App\Helper
 */
class StatusList
{
    /**
     * Lang instance
     * @var Lang
     */
    protected Lang $lang;

    /**
     * Create new instance
     * @param Lang $lang
     */
    public function __construct(
        Lang $lang
    ) {
        $this->lang = $lang;
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getYesNoStatus(): array
    {
        return [
            YesNoStatus::YES => $this->lang->tr('Oui'),
            YesNoStatus::NO => $this->lang->tr('Non'),
        ];
    }

    /* DATA DEFINITIONS START */

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionImportStatus(): array
    {
        return [
            DataDefinitionImportStatus::PENDING => $this->lang->tr('En attente'),
            DataDefinitionImportStatus::PROCESSED => $this->lang->tr('Importé'),
            DataDefinitionImportStatus::CANCELLED => $this->lang->tr('Annulé'),
            DataDefinitionImportStatus::ERROR => $this->lang->tr('Erreur'),
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionDirection(): array
    {
        return [
            DataDefinitionDirection::IN => $this->lang->tr('Import'),
            DataDefinitionDirection::OUT => $this->lang->tr('Export'),
        ];
    }


    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionRepository(): array
    {
        return [
            DemoRepository::class => $this->lang->tr('Demo'),
            UserRepository::class => $this->lang->tr('Utilisateurs'),
            DataDefinitionRepository::class => $this->lang->tr('Définitions des données'),
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionFilter(): array
    {
        return [
            UserFilter::class => $this->lang->tr('Utilisateurs'),
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionLoader(): array
    {
        return [
            'json_file_loader' => $this->lang->tr('Fichier (JSON)'),
            'csv_file_loader' => $this->lang->tr('Fichier (CSV)'),
            'pdf_file_loader' => $this->lang->tr('Fichier (PDF)'),
            'entity_import_loader' => $this->lang->tr('Importation entité'),
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionExtractor(): array
    {
        return [
            'csv_file_extractor' => $this->lang->tr('Fichier (CSV)'),
            'repository_extractor' => $this->lang->tr('Modèle (Repository)'),
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionTransformer(): array
    {
        return [
            'entity_import_transformer' => $this->lang->tr('Formattage entité (import)'),
            'entity_transformer' => $this->lang->tr('Formattage (Entité)'),
        ];
    }

    /* DATA DEFINITIONS END */
}
