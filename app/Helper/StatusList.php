<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\App\Enum\DataDefinitionDirection;
use Platine\App\Enum\YesNoStatus;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
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
    public function getYesNoStatus(): array
    {
        return [
            YesNoStatus::YES => $this->lang->tr('Oui'),
            YesNoStatus::NO => $this->lang->tr('Non'),
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionRepository(): array
    {
        return [
            UserRepository::class => $this->lang->tr('Utilisateurs'),
            DataDefinitionFieldRepository::class => $this->lang->tr('Définitions des données'),
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
        ];
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getDataDefinitionExtractor(): array
    {
        return [
            'db_extractor' => $this->lang->tr('Requete SQL'),
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
            'simple_transformer' => $this->lang->tr('Formattage (simple)'),
            'entity_transformer' => $this->lang->tr('Formattage (Entité)'),
        ];
    }
}
