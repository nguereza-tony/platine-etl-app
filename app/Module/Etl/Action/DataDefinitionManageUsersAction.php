<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Auth\Repository\UserRepository;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Param\DataDefinitionUserParam;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionUserRepository;
use Platine\Database\Connection;
use Platine\Http\ResponseInterface;
use Platine\Stdlib\Helper\Arr;

/**
* @class DataDefinitionManageUsersAction
* @package Platine\App\Module\Etl\Action
*/
class DataDefinitionManageUsersAction extends BaseAction
{
    /**
    * The ActionHelper instance
    * @var ActionHelper
    */
    protected ActionHelper $actionHelper;

    /**
    * The DataDefinitionRepository instance
    * @var DataDefinitionRepository
    */
    protected DataDefinitionRepository $dataDefinitionRepository;

    /**
    * The DataDefinitionUserRepository instance
    * @var DataDefinitionUserRepository
    */
    protected DataDefinitionUserRepository $dataDefinitionUserRepository;

    /**
    * The UserRepository instance
    * @var DataDefinitionRepository
    */
    protected UserRepository $userRepository;

    /**
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionUserRepository $dataDefinitionUserRepository
    * @param UserRepository $userRepository
    * @param Connection $connection
    * @param DataDefinitionRepository $dataDefinitionRepository
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionUserRepository $dataDefinitionUserRepository,
        UserRepository $userRepository,
        Connection $connection,
        DataDefinitionRepository $dataDefinitionRepository
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionUserRepository = $dataDefinitionUserRepository;
        $this->userRepository = $userRepository;
        $this->connection = $connection;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('etl/definition/users');

        $request = $this->request;
        $param = $this->param;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinition|null $dataDefinition */
        $dataDefinition = $this->dataDefinitionRepository->find($id);

        if ($dataDefinition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }

        $this->addContext('definition', $dataDefinition);

        $users = $this->userRepository->orderBy(['lastname', 'firstname'])
                                      ->all();

        $this->addContext('users', $users);

        $userList = $this->dataDefinitionUserRepository->query()
                                                       ->filter(['definition' => $id])
                                                        ->all(['user_id'], false);

        $currentUsersId = Arr::getColumn($userList, ['user_id']);

        $this->addContext('param', (new DataDefinitionUserParam())->setUsers($currentUsersId));

        if ($request->getMethod() === 'GET') {
            return $this->viewResponse();
        }

        $formParam = new DataDefinitionUserParam($param->posts());
        $this->addContext('param', $formParam);

        //Handle users
        $usersId = $formParam->getUsers();
        $usersIdToDelete = array_diff($currentUsersId, $usersId);
        if (!empty($usersIdToDelete)) {
            $deletedUsers = $this->userRepository->findAll(...$usersIdToDelete);
            $dataDefinition->removeUsers($deletedUsers);
        }

        $newUsersId = array_diff($usersId, $currentUsersId);
        if (!empty($newUsersId)) {
            $newUsers = $this->userRepository->findAll(...$newUsersId);
            $dataDefinition->setUsers($newUsers);
        }

        $this->connection->startTransaction();
        try {
            $this->dataDefinitionRepository->save($dataDefinition);

            $this->connection->commit();
            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $id]);
        } catch (Exception $ex) {
            $this->connection->rollback();
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
