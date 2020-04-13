<?php

include_once '../src/TaskModel.php';

class Controller
{
    /**
     * @var $config array
     * routes config
     */
    protected $config;
    /**
     * @var $params array
     * path dynamic part, like /entity/:id
     */
    protected $params;
    /** body of request*/
    protected $body;
    /**
     * @var TaskModel
     */
    protected $model;

    public function __construct($config)
    {
        $this->config = $config;
        $this->body = json_decode(file_get_contents('php://input'));
        $this->model = new TaskModel();
    }

    protected function resolveAction(): string
    {
        $pathURL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($this->config as $item) {
            if ($item['method'] !== strtolower($_SERVER['REQUEST_METHOD'])) continue;
            if (!preg_match($item['pattern'], $pathURL, $matches)) continue;
            $this->params = $matches;
            return $item['action'];
        }
        return 'notFound';
    }

    public function handle()
    {
        $action = ($this->resolveAction()) . 'Action';
        if (!method_exists($this, $action)) return $this->notFoundAction();
        return $this->$action();
    }

    public function notFoundAction()
    {
        http_response_code(404);
        return json_encode(['error' => 'page_not_found', 'status' => '404']);
    }


    /* === actions === */
    public function getTasksAction()

    {
        $sort = in_array($_GET['sort'], ['name', 'email', 'done']) ? $_GET['sort'] : 'email';
        $order = in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc';
        $page = (int)$_GET['page'] ? (int)$_GET['page'] - 1 : 0;
        // echo 'sort---->'.$sort;die;
        $offset = $page * 3;


        $tasks = $this->model->getTasks($sort, $order, $offset);
        $cnt = $this->model->count();
        $pages = (int)($cnt / 3) + ($cnt % 3 ? 1 : 0);
        return json_encode(['payload' => ['tasks' => $tasks, 'pages' => $pages], 'success' => true]);
    }

    public function getTaskItemAction()
    {
        $result = $this->model->find($this->params['taskid']);
        return json_encode(['result' => true, 'payload' => $result]);
    }

    public function createTaskAction()
    {
        $newTaskId = $this->model->create($this->body->task);
        $cnt = $this->model->count();
        return json_encode([
            'result' => true,
            'payload' => [
                'id' => $newTaskId, 'pages' => (int)($cnt / 3) + ($cnt % 3 ? 1 : 0)
            ]
        ]);
    }

    public function editTaskAction()
    {
        /** checking access  */
        if (!$_SESSION['is_admin']) return json_encode(['denied' => true]);
        /** updating task */
        $text = $this->body->text;
        $id = $this->params['taskid'];
        $task = $this->model->updateText($text, $id);
        return json_encode(['success' => true, 'payload' => $task]);
    }

    public function setConfirmedAction()
    {
        $id = $this->params['taskid'];
        $status = $this->body->confirmed;
        $r = $this->model->updateStatus($status, $id);
        return json_encode(['success' => $r]);
    }

    public function loginAction()
    {
        if ($this->body->login === 'admin' && $this->body->password === '123') {
            $_SESSION['is_admin'] = true;
            return json_encode(['success' => true, 'payload' => 'permitted']);
        }
        return json_encode(['success' => false, 'payload' => 'denied']);

    }

    public function logoutAction()
    {
        session_destroy();
        return json_encode(['success' => true]);

    }

    public function checkAccessAction()
    {
        return json_encode(['isAdmin' => $_SESSION['is_admin']]);
    }
}