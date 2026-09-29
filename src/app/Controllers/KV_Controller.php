<?php

/**
 * Override Method BaseController Codeigniter 4
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-14
 */

namespace App\Controllers;

use App\Libraries\System\SystemLib;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Files\File;
use Config\Database;
use Exception;
use stdClass;

class KV_Controller extends BaseController
{
    /**
     * Global variable to store the database connection instance.
     * Used for interacting with the database for queries and transactions.
     */
    protected $db;

    /**
     * Global variable to store the validation service instance.
     * Used for handling form validation or custom validation logic.
     */
    protected $validator;

    /**
     * Global variable to store access logs.
     * This can be used to set access logs.
     */
    protected $_log_access = FALSE;

    /**
     * Global variable to store system library.
     * This can be used to identify records or transactions.
     */
    protected $sys_lib;

    /**
     * Global variable to store the login flag.
     * If set to true, login verification will be triggered.
     */
    protected $_login_protected;

    /**
     * Global variable to store the current URL or URI name.
     * This variable holds the URI segment or path used to identify the current page or route.
     */
    protected $_uri_name;

    /**
     * Global variable to store the folder or directory URI name.
     * This variable holds the URI segment that corresponds to the folder or directory name
     * for the view being rendered.
     */
    protected $_uri_name_view;

    /**
     * Global variable to store the menu code.
     * This variable holds the specific code representing the menu being accessed or displayed.
     */
    protected $_menu_code = NULL;

    /**
     * Global variable to store a generated UUID.
     * This can be used to uniquely identify records or transactions.
     */
    protected $uuid;

    /**
     * Global variable to store the page title.
     * Holds the title of the page to be displayed in the browser or template.
     */
    protected $_title;

    /**
     * Global variable to store the page title prefix.
     * This prefix is added to the page title for better context, such as the site name or section.
     */
    protected $_prefix_title = NULL;

    /**
     * Global variable to store additional data that will be passed to the view for rendering.
     * This data can be used to customize or extend the view with extra information.
     */
    protected $_additional_display = array();

    /**
     * Global variable to store messages that will be passed to the view.
     */
    protected $message;

    /**
     * Global variable to store the content view file to be loaded.
     * This variable holds the name of the view file that will be rendered and displayed to the user.
     */
    protected $_content_view;

    /**
     * Constructor to initialize required services and dependencies.
     */
    public function __construct($login_protected = TRUE)
    {
        /**
         *  Set default value for $_login_protected by constructor $login_protected parameter
         */
        $this->_login_protected = $login_protected;

        /**
         *  Initialize Database connection
         */
        $this->db = Database::connect();

        /**
         *  Initialize System Library
         */
        $this->sys_lib = new SystemLib();

        /**
         *  Initialize Validator service
         */
        $this->validator = \Config\Services::validation();

        /**
         *  Generate UUIDv4
         */
        $this->uuid = uuid_v4();

        /**
         *  Initialize view template base on $_uri_name
         */
        if (!empty($this->_uri_name_view)) {
            throw PageNotFoundException::forPageNotFound('View template not found.');
        }
    }

    /**
     * Method to handle default route page.
     * 
     * @return \CodeIgniter\View\View The rendered view with the prepared data.
     */
    public function getIndex()
    {
        return redirect()->to(site_url('home/dashboard'));
    }

    /**
     * Method to render the index page view.
     * 
     * This method loads the index page (or a custom content view) by gathering the necessary data,
     * such as URI name, page title, and menu code. It can also merge any additional data for display.
     * If no specific content view is set, it defaults to 'index'.
     * 
     * @param array $params Optional parameters for customization, such as an 'id' for editing.
     * 
     * @return \CodeIgniter\View\View The rendered view with the prepared data.
     */
    protected function _index($params = array())
    {
        if (empty($this->_content_view)) {
            $this->_content_view = 'index';
        }

        /**
         *  Parse data to view
         */
        $data = array(
            'uri_name'       => $this->_uri_name,
            'uri_name_js_id' => str_replace('/', '__', $this->_uri_name),
            'title'          => $this->_title,
            'menu_code'      => $this->_menu_code
        );

        if (!empty($this->_additional_display)) {
            $data = array_merge($data, $this->_additional_display);
        }

        /**
         *  Load view with file located in template_name/controller_name/index
         */
        $content = $this->_uri_name_view . '/' . $this->_content_view;

        return view($content, $data);
    }

    /**
     * Displays the detail page based on the retrieved data from the database.
     *
     * @param mixed $id The ID of the data to be displayed.
     * @return \CodeIgniter\HTTP\Response|string Returns the detail page view or throws an error if data is not found.
     * @throws PageNotFoundException If the ID is invalid or data is not found.
     */
    protected function _detail($id, $params = array(), $class = NULL)
    {
        if (empty($this->_content_view)) {
            $this->_content_view = 'detail';
        }

        $this->_prefix_title = 'Detail';

        /**
         * Validate ID
         */
        if (is_null($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        /**
         * Fetch the detail data based on the provided ID
         */
        if (!empty($class)) {
            $result = $this->$class($id, $params);
        } else {
            $result = $this->get_detail($id, $params);
        }

        if (empty($result)) {
            throw PageNotFoundException::forPageNotFound();
        }

        /**
         * Prepare data to be passed to the view
         */
        $data = [
            'uri_name'       => $this->_uri_name,
            'uri_name_js_id' => str_replace('/', '__', $this->_uri_name),
            'result'         => $result,
            'title'          => $this->_prefix_title . ' ' . $this->_title,
            'menu_code'      => $this->_menu_code
        ];

        /**
         * Merge additional display data if available
         */
        if (!empty($this->_additional_display)) {
            $data = array_merge($data, $this->_additional_display);
        }

        /**
         * Define the view path based on the controller and content view
         */
        $content = $this->_uri_name_view . '/' . $this->_content_view;

        return view($content, $data);
    }

    /**
     * Method to load form input and edit form.
     * 
     * This method is responsible for rendering a form view. It handles both the creation and editing of data,
     * depending on whether an `id` is provided in the `$params` array. If an `id` is present, the form is loaded
     * with the data of the existing record, allowing the user to edit it. If no `id` is provided, a new record is created.
     * 
     * The form action is determined based on the presence of an `id`:
     * - For a new record, the action is set to 'create'.
     * - For an existing record, the action is set to 'edit/{id}' where `{id}` is the record's unique identifier.
     * 
     * Additional data (if provided) is merged with the form data before passing it to the view.
     * 
     * @param object $model The model object used to interact with the database.
     * @param array $params Optional parameters for the form, such as 'id' for editing.
     * 
     * @return \CodeIgniter\View\View The form view, rendered with the provided data.
     */
    protected function _form($model, $params = array())
    {
        if (empty($this->_content_view)) {
            $this->_content_view = 'form';
        }

        /**
         *  Set default method for form action
         */
        $form_action  = 'create';

        if ($this->_prefix_title === NULL)
            $this->_prefix_title = 'Tambah';

        $result         = NULL;

        if (!empty($params['id'])) {
            $form_action  = 'edit/' . $params['id'];
            $this->_prefix_title = 'Ubah';

            /**
             *  Get data from model
             */
            $result = $model->find($params['id']);

            if (empty($result)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        /**
         *  Parse data to view
         */
        $data = array(
            'uri_name'        => $this->_uri_name,
            'uri_name_js_id'  => str_replace('/', '__', $this->_uri_name),
            'form_action'     => $form_action,
            'result'          => $result,
            'title'           => (!empty($this->_prefix_title) ? $this->_prefix_title . " " . $this->_title : $this->_title),
            'menu_code'       => $this->_menu_code
        );

        /**
         *  Check additional data to parse to view
         */
        if (!empty($this->_additional_display)) {
            $data = array_merge($data, $this->_additional_display);
        }

        /**
         *  Load view with file located in template_name/controller_name/form
         */
        $content = $this->_uri_name_view . '/' . $this->_content_view;

        return view($content, $data);
    }

    /**
     * Inserts a new record into the specified model.
     *
     * This method retrieves input data, validates it, and inserts a new record into the database. 
     * It supports custom validation rules and messages. If the insertion is successful, it 
     * returns a success response; otherwise, it returns an error response.
     *
     * @param object $model The model instance used for data insertion.
     * @param string|null $redirect_url The URL to redirect after a successful insertion (optional).
     * @param array $params Additional parameters for data processing (optional).
     * @param array $validation Custom validation rules and messages (optional).
     * @param string $method The HTTP method used to retrieve input data (e.g., 'post', 'get').
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse JSON response containing status, message, and inserted data.
     * 
     * @throws \Exception If the insertion fails.
     */
    protected function _insert($model, $redirect_url = NULL, $params = array(), $validation = array(), $method = 'post')
    {
        $response                = new stdClass();
        $response->status_code   = 201;
        $response->message       = NULL;
        $response->data          = new stdClass();
        $response->redirect_url  = $redirect_url;

        $data = $this->_set_model_values($model, $method);
        $data['id'] = $this->uuid;

        if (isset($params['include_parent_id']) && $params['include_parent_id'] === TRUE) {
            $response->redirect_url = $response->redirect_url . '/' . $data[$params['parent_id']];
        }

        if (count($validation) > 0) {
            $this->validator->setRules($validation['rules'], $validation['message']);
        } else {
            $this->validator->setRules($model->validationRules, $model->validationMessages);
        }

        if (!$this->validator->run($data)) {
            return $this->response_error($this->validator->getErrors());
        }

        $this->db->transBegin();

        try {
            // Unique field validation
            if (!empty($model->uniqueFields)) {
                try {
                    $this->_validate_unique_fields($model, $model->uniqueFields, $data);
                } catch (Exception $e) {
                    // If there is an unique error validation, return response error
                    $errors = json_decode($e->getMessage(), true);

                    return $this->response_error($errors);
                }
            }

            $response->data = $data;

            $model->save($data);

            if ($this->db->transStatus() === false) {
                throw new Exception('Terjadi kesalahan saat menyimpan data.');
            }

            $this->db->transCommit();

            if (session()->get('login_user_id')) {
                $this->_log_access = TRUE;

                $this->set_log_access();
            }

            return $this->response_success($response);
        } catch (Exception $e) {
            $this->db->transRollback();

            return $this->response_error($e->getMessage());
        }
    }

    /**
     * Updates an existing record in the specified model.
     *
     * This method retrieves input data, validates it, and updates the corresponding record 
     * in the database. It supports unique field validation and handles transactions to ensure 
     * data integrity. If the update is successful, it returns a success response; otherwise, 
     * it rolls back the transaction and returns an error response.
     *
     * @param object $model The model instance used to update data.
     * @param string|null $redirect_url The URL to redirect after a successful update (optional).
     * @param array $params Parameters containing the record ID and other necessary data.
     * @param array $validation Custom validation rules and messages (optional).
     * @param string $method The HTTP method used to retrieve input data (e.g., 'post', 'get').
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse JSON response containing status, message, and updated data.
     * 
     * @throws \Exception If the record is not found or the update fails.
     */
    protected function _update($model, $redirect_url = NULL, $params = array(), $validation = array(), $method = 'post')
    {
        $response                = new stdClass();
        $response->status_code   = 200;
        $response->message       = NULL;
        $response->data          = new stdClass();
        $response->redirect_url  = $redirect_url;

        $data = $this->_set_model_values($model, $method);
        unset($data['id']);

        if (isset($params['include_parent_id']) && $params['include_parent_id'] === TRUE) {
            $response->redirect_url = $response->redirect_url . '/' . $data[$params['parent_id']];
        }

        if (count($validation) > 0) {
            $this->validator->setRules($validation['rules'], $validation['message']);
        } else {
            $this->validator->setRules($model->validationRules, $model->validationMessages);
        }

        if (!$this->validator->run($data)) {
            return $this->response_error($this->validator->getErrors());
        }

        $this->db->transBegin();

        try {
            // Unique field validation
            if (!empty($model->uniqueFields)) {
                try {
                    $this->_validate_unique_fields($model, $model->uniqueFields, $data, $params['id']);
                } catch (Exception $e) {
                    // If there is an unique error validation, return response error
                    $errors = json_decode($e->getMessage(), true);

                    return $this->response_error($errors);
                }
            }

            $existingData = $model->find($params['id']);

            if (!$existingData) {
                throw new \Exception('Data tidak ditemukan.');
            }

            $response->data = $data;

            $model->update($params['id'], $data);

            if ($this->db->transStatus() === false) {
                throw new \Exception('Terjadi kesalahan saat memperbarui data.');
            }

            $this->db->transCommit();

            if (session()->get('login_user_id')) {
                $this->_log_access = TRUE;

                $this->set_log_access();
            }

            return $this->response_success($response);
        } catch (\Exception $e) {
            $this->db->transRollback();

            return $this->response_error($e->getMessage());
        }
    }

    /**
     * Method to delete data in the model
     * @param object $model - The model to delete data
     * @param string $redirect_url - The URL to redirect after success
     * @param array $params - Parameters, including ID
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    protected function _delete($model, $redirect_url = NULL, $params = array())
    {
        $response                = new stdClass();
        $response->status_code   = 201;
        $response->message       = NULL;
        $response->data          = new stdClass();
        $response->redirect_url  = $redirect_url;

        $this->db->transBegin();

        try {
            $group = $model->find($params['id']);

            if (!$group) {
                throw new \Exception('Data tidak ditemukan.');
            }

            $model->delete($params['id']);

            if ($this->db->transStatus() === false) {
                throw new \Exception('Gagal menghapus data.');
            }

            $this->db->transCommit();

            if (session()->get('login_user_id')) {
                $this->_log_access = TRUE;

                $this->set_log_access();
            }

            return $this->response_success($response);
        } catch (\Exception $e) {
            $this->db->transRollback();

            return $this->response_error($e->getMessage());
        }
    }

    /**
     * Executes a function within a specified class with validation and database transaction support.
     *
     * @param string      $class         The name of the class containing the function to be executed.
     *                                   If 'this', the function will be executed within the current class.
     * @param string      $function_name The name of the function to be executed.
     * @param string|null $redirect_url  The URL to redirect to after successful execution (optional).
     * @param array       $params        Parameters to be passed to the function being executed (optional).
     * @param array       $validation    Validation rules before executing the function (optional).
     *                                   Format:
     *                                   [
     *                                      'rules'   => [...], // Validation rules
     *                                      'message' => [...]  // Custom error messages
     *                                   ]
     * @param string      $method        The HTTP method used, default is 'post' (optional).
     *
     * @return object Returns a success response if the execution is successful, 
     *               or an error response if an error occurs.
     */
    protected function _execute($class, $function_name, $redirect_url = NULL, $params = array(), $validation = array(), $method = 'post')
    {
        $response                = new stdClass();
        $response->status_code   = 200;
        $response->message       = NULL;
        $response->data          = new stdClass();
        $response->redirect_url  = $redirect_url;

        if (!empty($validation) && !$this->validate($validation['rules'], $validation['message'])) {
            return $this->response_error($this->validator->getErrors());
        }

        $this->db->transBegin();

        try {
            if ($class == 'this') {
                $response->data = call_user_func_array(array($this, $function_name), $params);
            } else {
                $response->data = call_user_func_array(array($this->$class, $function_name), $params);
            }

            if ($this->db->transStatus() === false) {
                throw new Exception('Terjadi kesalahan saat menyimpan atau menghapus data.');
            } else {
                $this->db->transCommit();

                if (session()->get('login_user_id')) {
                    $this->_log_access = TRUE;

                    $this->set_log_access();
                }

                return $this->response_success($response);
            }
        } catch (Exception $e) {
            $this->db->transRollback();

            return $this->response_error($e->getMessage());
        }
    }

    protected function _set_model_values($model, $resource = "post", $source = NULL)
    {
        $result = array();

        foreach ($model->allowedFields as $field) {
            $field_value = $this->_get_field_value($field, $resource, $source);

            if ($field_value === "" || $field_value === NULL) {
                $field_value = NULL;
            }

            $result[$field] = $field_value;
        }

        unset($result['id']);

        return $result;
    }

    /**
     * Retrieve a specific field value from different request sources.
     *
     * This function extracts a field value based on the specified source method, 
     * allowing flexibility in handling different request types such as GET, POST, 
     * or even from arrays and objects.
     *
     * @param string $field - The name of the field to retrieve.
     * @param string $resource - The data source to fetch from ('post', 'get', 'get_post', 'post_get', 'array', 'object').
     * @param array|object|null $source - The source array or object when using 'array' or 'object' as the resource.
     * 
     * @return mixed|null - Returns the field value if found, otherwise NULL.
     */
    protected function _get_field_value($field, $resource = 'post', $source = NULL)
    {
        switch ($resource) {
            case 'get_post':
                return $this->request->getPostGet($field);
            case 'post_get':
                return $this->request->getGetPost($field);
            case 'post':
                return $this->request->getPost($field);
            case 'get':
                return $this->request->getGet($field);
            case 'array':
                $value = (isset($source[$field]) ? $source[$field] : FALSE);

                if ($value === FALSE)
                    $value = NULL;

                return $value;
            case 'object':
                $value = (isset($source->$field) ? $source->$field : FALSE);

                if ($value === FALSE)
                    $value = NULL;

                return $value;
            default:
                return NULL;
        }
    }

    /**
     * Method to retrieve form values based on HTTP method
     * @param string $method - The HTTP method ('post', 'get', etc.)
     * @return array|NULL
     */
    protected function _get_field_values($method = 'post')
    {
        switch ($method) {
            case 'get_post':
                return $this->request->getPostGet();
            case 'post_get':
                return $this->request->getGetPost();
            case 'post':
                return $this->request->getPost();
            case 'get':
                return $this->request->getGet();
            default:
                return NULL;
        }
    }

    /**
     * Retrieves and restructures data from the database.
     * 
     * This function serves as a default method where the return value is NULL by default. 
     * Therefore, this function **must** be overridden in the controller according to its specific functionality. 
     *
     * @param mixed $id The ID of the data to be loaded.
     * @return object|NULL The query result as a mapped object or NULL if not overridden.
     */
    protected function get_detail($id)
    {
        $result = NULL;

        return $result;
    }

    /**
     * Validate unique fields in a database table to prevent duplication.
     *
     * @param object $model - The model instance containing table and unique label definitions.
     * @param array $unique_fields - An array of field names (or sets of field names) that must be unique.
     * @param array $data - The input data containing field values to be checked.
     * @param int|string|null $except_id - The ID to exclude from the uniqueness check (used during updates).
     * 
     * @throws Exception - Throws an exception containing JSON-encoded error messages if duplicates are found.
     */
    protected function _validate_unique_fields($model, $unique_fields, $data, $except_id = NULL)
    {
        $unique_errors = [];

        foreach ($unique_fields as $_unique_fields) {
            // Make sure field is array value
            if (!is_array($_unique_fields)) {
                $_unique_fields = [$_unique_fields];
            }

            $unique_fields_value = [];
            $message_values = [];

            foreach ($_unique_fields as $field) {
                // Get field value from data
                $value = $data[$field] ?? NULL;
                if ($value !== NULL) {
                    $unique_fields_value[$field] = $value;
                    $message_values[$field] = $model->unique_labels[$field] ?? $value;
                } else {
                    $message_values[$field] = $model->unique_labels[$field] ?? $field;
                }
            }

            if (count($unique_fields_value) > 0) {
                $builder = $this->db->table($model->table);

                foreach ($unique_fields_value as $field => $value) {
                    $builder->where($field, $value);
                }

                // If in update state, ignore current ID
                if (!empty($except_id)) {
                    $builder->where("id !=", $except_id);
                }

                // Check duplicate data
                if ($builder->countAllResults() > 0) {
                    foreach ($unique_fields_value as $field => $value) {
                        $unique_errors[$field] = $value . ' ditemukan duplikasi';
                    }
                }
            }
        }

        // If there is an error, throw exception as array
        if (!empty($unique_errors)) {
            throw new Exception(json_encode($unique_errors));
        }
    }

    /**
     * Validate unique username in a user table to prevent duplication.
     *
     * @param object $model - The model instance containing table and unique username.
     * @param string $username - Username to validate
     * @param string|null $except_id - Except id when edit (optional)
     * @throws Exception - If username duplication found
     * @return bool - True if username is unique
     */
    protected function _validate_unique_username($model, $username, $except_id = NULL)
    {
        $encrypted_username = kv_encrypt($username, getenv('APP_KEY'));

        $query = $model->where('username', $encrypted_username);

        if ($except_id !== NULL) {
            $query->where('id !=', $except_id);
        }

        $existing_user = $query->first();

        if ($existing_user) {
            throw new Exception('Username: ' . $username . ' ditemukan duplikasi');
        }

        return true;
    }

    protected function upload_file($file, $path, $file_name)
    {

        if (! $file->hasMoved()) {
            $file_path = WRITEPATH . 'uploads/' . $file->store();

            $data = ['uploaded_fileinfo' => new File($file_path)];

            return $data;
        }

        // if ($file->isValid() && !$file->hasMoved()) {
        //     $upload_path = FCPATH . 'storage/' . $path;

        //     if (!is_dir($upload_path)) {
        //         mkdir($upload_path, 0755, true);
        //     }

        //     if ($file->move($upload_path, $file_name)) {
        //         return true;
        //     }
        // }

        // return false;
    }

    /**
     * Handle error response and return as JSON
     * @param Exception|string $e - Exception or error message
     */
    protected function response_error($e)
    {
        $message = 'An error occurred, please try again later.';
        if (is_array($e)) {
            $message = 'Validation failed';
            $data = $e;
        } elseif ($e instanceof Exception) {
            if (ENVIRONMENT === 'development') {
                $message = $e->getMessage();
                $message .= ' in line ' . $e->getLine() . ' at ' . $e->getFile();
                $data = $e->getTrace();
            }
        } else {
            $message = $e;
        }

        return $this->response->setStatusCode(500)
            ->setJSON([
                'status'  => 'error',
                'message' => $message,
                'errors'  => $data ?? NULL
            ]);
    }

    /**
     * Handle a successful response and return it as JSON.
     *
     * @param object $response - Response object containing status code, success flag, message, and optional redirect URL.
     * @return \CodeIgniter\HTTP\Response - JSON response with HTTP status code.
     */
    protected function response_success($response)
    {
        if ((!isset($response->message) or empty($response->message)) && !empty($this->message)) {
            $response->message = $this->message;
        }

        return $this->response->setStatusCode($response->status_code ?? 200)
            ->setJSON([
                'status'       => 'success',
                'message'      => $response->message,
                'redirect_url' => (!empty($response->redirect_url)) ? site_url($response->redirect_url) : NULL,
                'data'         => $response->data,
            ]);
    }

    protected function set_log_access()
    {
        $is_saved = TRUE;
        $acl_user_id = session()->get('login_user_id');

        $useable_account = $this->sys_lib->get_sys_reference('allowed_organization_configuration') ?? '[]';
        $useable_account_list = json_decode(str_replace("'", '"', $useable_account), TRUE);

        if (in_array($acl_user_id, $useable_account_list ?? [])) {
            $is_saved = false;
        }

        if ($is_saved === TRUE) {
            $log_id = uuid_v4();

            $data = [
                'id'          => $log_id,
                'acl_user_id' => $acl_user_id,
                'ip_address'  => $this->request->getIPAddress(),
                'user_data'   => $this->request->getUserAgent(),
                'url'         => current_url(),
                'month'       => date('m'),
                'year'        => date('Y'),
            ];

            $this->db->table('log.access')->insert($data);

            return $log_id;
        }

        return null;
    }
}
