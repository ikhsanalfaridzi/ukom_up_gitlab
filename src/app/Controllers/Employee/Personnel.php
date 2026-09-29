<?php

/**
 * Personnel Feature from Employee Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-08
 */

namespace App\Controllers\Employee;

use App\Controllers\KV_Controller;
use App\Models\Acl\GroupModel;
use App\Models\Acl\UserModel;
use App\Models\Employee\AssortmentModel;
use App\Models\Employee\GradeModel;
use App\Models\Employee\PersonnelModel;
use App\Models\Identity\PersonalModel;
use App\Models\Instance\OrganizationModel;
use App\Models\Instance\OrganizationStructureModel;
use App\Models\Instance\PositionModel;
use App\Models\Reference\EmploymentModel;
use App\Libraries\Acl\UserLib;
use App\Models\Acl\UserGroupModel;
use App\Models\Reference\EmploymentTypeModel;
use Config\Services;
use Exception;

class Personnel extends KV_Controller
{
    protected $personnel;
    protected $personal;
    protected $user;
    protected $user_group;
    protected $employment;
    protected $employment_type;
    protected $assortment;
    protected $grade;
    protected $position;
    protected $organization;
    protected $organization_structure;
    protected $group;
    protected $user_lib;

    public function __construct()
    {
        parent::__construct(TRUE);
        $this->personnel       = new PersonnelModel();
        $this->personal        = new PersonalModel();
        $this->user            = new UserModel();
        $this->user_group      = new UserGroupModel();
        $this->employment      = new EmploymentModel();
        $this->employment_type = new EmploymentTypeModel();
        $this->assortment      = new AssortmentModel();
        $this->grade          = new GradeModel();
        $this->position        = new PositionModel();
        $this->organization    = new OrganizationModel();
        $this->organization_structure = new OrganizationStructureModel();
        $this->group           = new GroupModel();
        $this->user_lib        = new UserLib();
        $this->_title          = 'Daftar Pegawai';
        $this->_uri_name       = 'employee/personnel';
        $this->_uri_name_view  = 'employee/personnel';
        $this->_menu_code      = 'employee_personnel';
    }

    public function getIndex()
    {
        if (!is_authorized($this->_menu_code, 'view') || !is_authorized($this->_menu_code, 'all')) {
            return access_denied();
        }

        return parent::_index();
    }

    public function postData()
    {
        if (!is_authorized($this->_menu_code, 'view') || !is_authorized($this->_menu_code, 'all')) {
            return access_denied();
        }

        $list = $this->personnel->get_datatables();
        $data = [];

        foreach ($list as $item) {
            try {
                $username = !empty($item->username) ? kv_decrypt($item->username, getenv('APP_KEY')) : '';
            } catch (\Throwable $e) {
                $username = $item->username ?? '';
            }

            $data[] = [
                'id'                => $item->id,
                'username'          => $username,
                'employee_number'   => $item->employee_number,
                'full_name'         => $item->full_name,
                'assortment'        => $item->assortment,
                'tingkatan'         => $item->tingkatan ?? '',
                'position'          => $item->position,
                'organization'      => $item->organization,
                'is_active'         => $item->is_active,
            ];
        }

        $response = [
            'draw'              => $this->request->getVar('draw'),
            'recordsTotal'      => $this->personnel->count_all(),
            'recordsFiltered'   => $this->personnel->count_filtered(),
            'data'              => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getVerification()
    {
        if (!is_authorized($this->_menu_code, 'verification')) {
            return access_denied();
        }

        $this->_content_view = 'verification';

        return parent::_index();
    }

    public function getVerification_list($is_verified)
    {
        if (!is_authorized($this->_menu_code, 'verification')) {
            return access_denied();
        }

        $this->_content_view = 'verification_list';

        $this->_additional_display['is_verified'] = $is_verified;

        return parent::_index();
    }

    public function postVerification_data($is_verified)
    {
        if (!is_authorized($this->_menu_code, 'verification')) {
            return access_denied();
        }

        if ($this->request->isAJAX()) {
            $list = $this->personnel->get_verification_data($is_verified);
            $data = [];

            foreach ($list as $item) {
                $full_name = array_filter([
                    $item->front_title ?? '',
                    $item->full_name ?? '',
                    $item->back_degree ?? '',
                    $item->non_academic_degree ?? ''
                ]);

                $data[] = [
                    'id'                => $item->id,
                    'employee_number'   => $item->employee_number,
                    'full_name'         => implode(' ', $full_name),
                    'assortment'        => $item->assortment . " - " . $item->description,
                    'position'          => $item->position,
                    'organization'      => $item->organization,
                    'employment'        => $item->employment,
                    'employment_type'   => $item->employment_type,
                    'is_verified'       => $item->is_verified,
                ];
            }

            $response = [
                'draw'              => $this->request->getVar('draw'),
                'recordsTotal'      => $this->personnel->count_verification_data($is_verified),
                'recordsFiltered'   => $this->personnel->count_verification_filtered($is_verified),
                'data'              => $data
            ];

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function postVerify($is_verified, $id)
    {
        if (!is_authorized($this->_menu_code, 'verification')) {
            return access_denied();
        }

        $this->message = 'Data berhasil diverifikasi!';

        $redirect_url = $this->_uri_name . '/verification';

        return parent::_execute('this', 'personnel_verify', $redirect_url, ['is_verified' => $is_verified, 'id' => $id]);
    }

    protected function personnel_verify($is_verified, $id)
    {
        try {
            $data = [
                'is_verified'    => $is_verified,
                'notes'          => $this->request->getPost('notes'),
                'verificator_id' => session()->get('login_user_id'),
                'verified_at'    => date('Y-m-d H:i:s'),
            ];

            // ========== Data update ==========
            if (!$this->personnel->update($id, $data)) {
                throw new Exception('Gagal melakukan verifikasi data.');
            }

            return $id;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getReject_form($id)
    {
        $this->_content_view = 'reject_form';

        $this->_additional_display['id'] = $id;

        return parent::_form($this->personnel, ['id' => $id]);
    }

    public function getVerification_detail($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'verification')) {
            return access_denied();
        }

        $this->_content_view = 'verification_detail';

        return parent::_detail($id, array(), 'get_verification_detail');
    }

    protected function get_verification_detail($id)
    {
        $result = $this->db->table('employee.personnel ep')
            ->select('ep.id, ep.employee_number, ep.office_email, ipr.full_name, ipr.front_title, ipr.back_degree, ipr.non_academic_degree, ipr.personal_email, ea.assortment, ea.description, ips.position, io.organization, pe.employment, pet.name as employment_type, ep.responsibility_letter_path, ep.is_verified, ep.verified_at, ip2.full_name as verificator_name')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'inner')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'inner')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'inner')
            ->join('instance.organization_structures os', 'ep.organization_structure_id = os.id', 'left')
            ->join('instance.organizations io', 'os.organization_id = io.id', 'left')
            ->join('public.employment pe', 'ep.employment_id = pe.id', 'inner')
            ->join('public.employment_type pet', 'ep.employment_type_id = pet.id', 'inner')
            ->join('acl.users au', 'ep.verificator_id = au.id', 'left')
            ->join('identity.personal ip2', 'ip2.acl_user_id = au.id', 'left')
            ->where('ep.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getDetail($id = NULL)
    {
        return parent::_detail($id);
    }

    protected function get_detail($id)
    {
        $result = $this->db->table('employee.personnel ep')
            ->select('ep.id, au.username, ep.employee_number, ipr.full_name, ea.assortment, pe.employment, ips.position, io.organization, au.is_active, ep.responsibility_letter_path, ep.created_at')
            ->join('employee.assortments ea', 'ep.assortment_id = ea.id', 'left')
            ->join('public.employment pe', 'ep.employment_id = pe.id', 'left')
            ->join('instance.positions ips', 'ep.position_id = ips.id', 'left')
            ->join('instance.organization_structures os', 'ep.organization_structure_id = os.id', 'left')
            ->join('instance.organizations io', 'os.organization_id = io.id', 'left')
            ->join('identity.personal ipr', 'ipr.employee_personnel_id = ep.id', 'left')
            ->join('acl.users au', 'ipr.acl_user_id = au.id', 'left')
            ->where('ep.id', $id)
            ->get()
            ->getRowArray();

        return $result;
    }

    public function getUpdate($id = NULL)
    {
        if (!is_authorized($this->_menu_code, 'update') || !is_authorized($this->_menu_code, 'self')) {
            return access_denied();
        }

        $personnel_id = session()->get('login_personnel_id');

        $this->_content_view = 'update';

        $result = $this->personnel->select('ip.full_name, ip.front_title, ip.back_degree, ip.non_academic_degree, ip.photo_path, ip.personal_email, ip2.full_name as verificator_name, ep.*')
            ->join('identity.personal ip', 'ip.employee_personnel_id = ep.id', 'inner')
            ->join('acl.users au', 'ep.verificator_id = au.id', 'left')
            ->join('identity.personal ip2', 'ip2.acl_user_id = au.id', 'left')
            ->where('ep.id', $personnel_id)
            ->from('employee.personnel ep')
            ->first();

        $employments = $this->employment->select('id, employment')->where('is_active', '1')->findAll();
        $employment_types = $this->employment_type->select('id, name')->where('is_active', '1')->findAll();
        $assortments = $this->assortment->select('id, assortment, description')->findAll();
        $positions = $this->position->select('id, position')->findAll();
        $organizations = $this->organization->select('io.id, io.organization')
            ->join('instance.groups ig', 'io.group_id = ig.id', 'inner')
            ->where('ig.sub_type !=', '0')
            ->where('ig.sub_type !=', '5')
            ->from('instance.organizations io')
            ->findAll();

        $front_titles = $this->personal->select('front_title')->distinct()->findAll();
        $back_degrees = $this->personal->select('back_degree')->distinct()->findAll();
        $non_academic_degrees = $this->personal->select('non_academic_degree')->distinct()->findAll();

        $assortment_options = [];
        $assortment_options[''] = '';

        foreach ($assortments as $item) {
            $assortment_options += [
                $item['id'] => $item['assortment'] . " - " . $item['description']
            ];
        }

        $this->_additional_display['employment_options'] = format_options($employments, 'employment');
        $this->_additional_display['employment_type_options'] = format_options($employment_types, 'name');
        $this->_additional_display['assortment_options'] = $assortment_options;
        $this->_additional_display['position_options'] = format_options($positions, 'position');
        $this->_additional_display['organization_options'] = format_options($organizations, 'organization');
        $this->_additional_display['front_title_options'] = format_options($front_titles, 'front_title', 'front_title');
        $this->_additional_display['back_degree_options'] = format_options($back_degrees, 'back_degree', 'back_degree');
        $this->_additional_display['non_academic_degree_options'] = format_options($non_academic_degrees, 'non_academic_degree', 'non_academic_degree');

        $this->_additional_display['result'] = $result;
        $this->_additional_display['personnel_id'] = $personnel_id;

        return parent::_form($this->personal, ['id' => $id]);
    }

    public function postUpdate()
    {
        if (!is_authorized($this->_menu_code, 'update') || !is_authorized($this->_menu_code, 'self')) {
            return access_denied();
        }

        $validations = [
            'rules'   => $this->personnel->validationRules,
            'message' => $this->personnel->validationMessages,
        ];

        unset($validations['rules']['employee_number']);
        unset($validations['message']['employee_number']);

        $this->message = 'Data berhasil disimpan';

        $redirect_url = 'identity/personal/update/' . session()->get('login_personnel_id');

        return parent::_execute('this', 'personnel_identity_update', $redirect_url, array(), $validations);
    }

    protected function personnel_identity_update()
    {
        try {
            /*
            * Personnel Data
            */
            $personnel_data = [
                'office_email'       => $this->request->getPost('office_email') === "" ? NULL : $this->request->getPost('office_email'),
                'employment_id'      => $this->request->getPost('employment_id'),
                'employment_type_id' => $this->request->getPost('employment_type_id'),
                'assortment_id'      => $this->request->getPost('assortment_id'),
                'position_id'        => $this->request->getPost('position_id'),
                'organization_structure_id'    => $this->request->getPost('organization_structure_id'),
                'is_verified'        => "0",
                'notes'              => NULL,
                'verificator_id'     => NULL,
                'verified_at'        => NULL,
            ];

            $personnel_id = $this->request->getPost('employee_personnel_id');
            $employee_number = $this->request->getPost('employee_number');

            $personnel = $this->personnel->select('id, employee_number, responsibility_letter_path')->where('id', $personnel_id)->first();

            $this->_validate_unique_fields($this->personnel, $this->personnel->uniqueFields, $personnel_data, $personnel['id']);

            // ========== Responsibility Letter ==========
            $letter = $this->request->getFile('responsibility_letter_path');
            if ($letter && $letter->isValid()) {
                $letter_path = 'dosier/' . $employee_number . '/personnel/responsibility_letter/';
                $letter_name = $letter->getRandomName();

                if (!empty($personnel['responsibility_letter_path'])) {
                    @unlink(WRITEPATH . 'uploads/' . $letter_path . $personnel['responsibility_letter_path']);
                }

                $personnel_data['responsibility_letter_path'] = $letter_name;
            } else {
                unset($personnel_data['responsibility_letter_path']);
            }

            // Update personnel data 
            if (!$this->personnel->update($personnel['id'], $personnel_data)) {
                throw new Exception('Gagal menyimpan data kepegawaian.');
            }

            /*
            * Personal Data
            */
            $personal_data = [
                'full_name'           => $this->request->getPost('full_name'),
                'personal_email'      => $this->request->getPost('personal_email') === "" ? NULL : $this->request->getPost('personal_email'),
                'front_title'         => $this->request->getPost('front_title'),
                'back_degree'         => $this->request->getPost('back_degree'),
                'non_academic_degree' => $this->request->getPost('non_academic_degree'),
            ];

            $personal = $this->personal->select('id, photo_path')->where('employee_personnel_id', $personnel_id)->first();

            // ========== Photo ==========
            $photo = $this->request->getFile('photo_path');
            if ($photo && $photo->isValid()) {
                $photo_path = WRITEPATH . 'uploads/dosier/' . $personnel["employee_number"] . '/personal/photo/';
                $photo_name = $photo->getRandomName();

                if (!empty($personal['photo_path'])) {
                    unlink($photo_path . $personal['photo_path']);
                }

                $personal_data['photo_path'] = $photo_name;
            }

            $this->_validate_unique_fields($this->personal, $this->personal->uniqueFields, $personal_data, $personal['id']);

            // Update personal data 
            if (!$this->personal->update($personal['id'], $personal_data)) {
                throw new Exception('Gagal menyimpan data identitas.');
            }

            if ($photo && $photo->isValid()) {
                if (!is_dir($photo_path)) {
                    mkdir($photo_path, 0777, true);
                }

                $photo = Services::image()
                    ->withFile($photo->getTempName())
                    ->resize(400, 600, true, 'height')
                    ->save($photo_path . '/' . $photo_name);

                if (!$photo) {
                    throw new Exception("Gagal mengunggah foto.");
                }
            }

            // ========== Responsibility Letter ==========
            if ($letter && $letter->isValid()) {
                if (!$letter->store($letter_path, $letter_name)) {
                    throw new Exception("Gagal mengunggah surat pertanggung jawaban mutlak.");
                }
            }

            return $personnel_id;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getForm($id = NULL)
    {
        $employments = $this->employment->select('id, employment')->where('is_active', '1')->findAll();
        $assortments = $this->assortment->select('id, assortment, description')->findAll();
        $positions = $this->position->select('id, position')->findAll();
        $grades = $this->grade->select('id, grade')->findAll();
        $organizations = $this->db->table('instance.organization_structures ios')
            ->select('ios.id, ios.structure_name')
            ->join('instance.organizations io', 'ios.organization_id = io.id', 'inner')
            ->join('instance.groups ig', 'ig.id = io.group_id', 'inner')
            ->whereNotIn('ig.sub_type', ["0", "5"])
            ->where('ios.is_active', '1')
            ->get()
            ->getResultArray();

        $groups = $this->group
            ->select('id, group_name')
            ->where('LOWER(group_name)', 'pegawai')
            ->findAll();

        if (!empty($id)) {
            $personnel = $this->personnel->select('old_employee_number, employee_number, employment_id, assortment_id, position_id, organization_structure_id')->find($id);
            $personal = $this->personal->select('full_name, personal_email, phone_number, wa_number, date_of_birth, gender, acl_user_id')->where('employee_personnel_id', $id)->first();
            $user = $this->user->select('id, username, is_active, is_change_password')->where('id', $personal['acl_user_id'])->first();
            $user_groups = $this->user_group->select('group_id')->where('user_id', $user['id'])->findAll();
            $group_ids = array_column($user_groups, 'group_id');

            $this->_additional_display['personnel'] = $personnel;
            $this->_additional_display['personal'] = $personal;
            $this->_additional_display['user'] = $user;
            $this->_additional_display['group_ids'] = $group_ids;
        }

        $assortment_options = [];
        $assortment_options[''] = '';

        foreach ($assortments as $item) {
            $assortment_options += [
                $item['id'] => $item['assortment'] . " - " . $item['description']
            ];
        }

        $this->_additional_display['employment_options'] = format_options($employments, 'employment');
        $this->_additional_display['assortment_options'] = $assortment_options;
        $this->_additional_display['position_options'] = format_options($positions, 'position');
        $this->_additional_display['grade_options'] = format_options($grades, 'grade');
        $this->_additional_display['organization_options'] = format_options($organizations, 'structure_name');
        $this->_additional_display['group_options'] = format_multiple_options($groups, 'group_name');
    
        return parent::_form($this->personnel, ['id' => $id]);
    }

    public function postCreate()
    {
        if (!is_authorized($this->_menu_code, 'insert')) {
            return access_denied();
        }

        $personal_rules = $this->personal->validationRules;
        unset(
            $personal_rules['place_of_birth'],
            $personal_rules['identity_address'],
            $personal_rules['address'],
            $personal_rules['identity_number'],
            $personal_rules['family_identity_number'],
            $personal_rules['photo_path'],
            $personal_rules['identity_card_path'],
            $personal_rules['province_id'],
            $personal_rules['city_id'],
            $personal_rules['district_id'],
            $personal_rules['village_id'],
            $personal_rules['religion_id'],
            $personal_rules['marital_status_id'],
            $personal_rules['employment_type_id'],
            $personal_rules['responsibility_letter_path']
        );

        $personal_messages = $this->personal->validationMessages;
        unset(
            $personal_messages['place_of_birth'],
            $personal_messages['identity_address'],
            $personal_messages['address'],
            $personal_messages['identity_number'],
            $personal_messages['family_identity_number'],
            $personal_messages['photo_path'],
            $personal_messages['identity_card_path'],
            $personal_messages['province_id'],
            $personal_messages['city_id'],
            $personal_messages['district_id'],
            $personal_messages['village_id'],
            $personal_messages['religion_id'],
            $personal_messages['marital_status_id'],
            $personal_messages['employment_type_id'],
            $personal_messages['responsibility_letter_path']
        );

        // Override Personal Model Validation Rules
        $this->personal->setValidationRules($personal_rules);
        $this->personal->setValidationMessages($personal_messages);

        $personnel_rules = $this->personnel->validationRules;
        unset(
            $personnel_rules['employment_type_id'],
            $personnel_rules['responsibility_letter_path']
        );

        $personnel_messages = $this->personnel->validationMessages;
        unset(
            $personnel_messages['employment_type_id'],
            $personnel_messages['responsibility_letter_path']
        );

        // Override Personnel Model Validation Rules
        $this->personnel->setValidationRules($personnel_rules);
        $this->personnel->setValidationMessages($personnel_messages);

        $validations = [
            'rules'   => array_merge(
                $personal_rules,
                $personnel_rules,
                // $this->user->validationRules
            ),
            'message' => array_merge(
                $personal_messages,
                $personnel_messages,
                // $this->user->validationMessages
            ),
        ];

        $this->message = 'Data berhasil disimpan';

        return parent::_execute('this', 'personnel_identity_and_account_add', $this->_uri_name, [], $validations);
    }

    protected function personnel_identity_and_account_add()
    {
        /*
        * Personnel Data
        */
        $personnel_data = [
            'employee_number'     => $this->request->getPost('employee_number'),
            'employment_id'       => $this->request->getPost('employment_id'),
            'assortment_id'       => $this->request->getPost('assortment_id'),
            'position_id'         => $this->request->getPost('position_id'),
            'organization_structure_id' => $this->request->getPost('organization_structure_id'),
        ];
        $personnel_data['id'] = $this->uuid;

        $this->_validate_unique_fields($this->personnel, $this->personnel->uniqueFields, $personnel_data);

        // Save personnel data 
        if (!$this->personnel->save($personnel_data)) {
            $errors = $this->personnel->errors();
            log_message('error', json_encode($errors));
            throw new Exception('Gagal menyimpan data kepegawaian.');
        }

        /*
        * User Data
        */
        $user_data = $this->_set_model_values($this->user, 'post');
        $user_data['id'] = $this->uuid;

        // Unique username validation
        try {
            $this->_validate_unique_username($this->user, $user_data['username']);
        } catch (Exception $e) {
            // If there is an unique error validation, return response error
            throw new Exception($e->getMessage());
        }

        $group_ids = $this->request->getPost('group_id[]');
        $group_names = empty($group_ids) ? [] : $this->group->select('group_name')->whereIn('id', $group_ids)->findAll();

        // Derive email & role from personal data and selected group access
        $user_data['email'] = $this->request->getPost('personal_email') ?: NULL;
        $user_data['role']  = empty($group_names) ? NULL : implode(', ', array_column($group_names, 'group_name'));

        // Save user data
        if (!$this->user->protect(false)->save($user_data)) {
            $errors = $this->user->errors();
            log_message('error', json_encode($errors));
            throw new Exception('Gagal menyimpan data user.');
        }

        // Assign user to group
        $this->user_lib->assign_user_groups($user_data['id'], $group_ids);

        /*
        * Personal Data
        */
        $personal_data = [
            'acl_user_id'           => $user_data['id'],
            'employee_personnel_id' => $personnel_data['id'],
            'full_name'             => $this->request->getPost('full_name'),
            'personal_email'        => $this->request->getPost('personal_email'),
            'phone_number'          => $this->request->getPost('phone_number'),
            'wa_number'             => $this->request->getPost('wa_number'),
            'gender'                => $this->request->getPost('gender'),
            'date_of_birth'         => $this->request->getPost('date_of_birth'),
        ];
        $personal_data['id'] = uuid_v4();

        $this->_validate_unique_fields($this->personal, $this->personal->uniqueFields, $personal_data);

        // Save personal data 
        if (!$this->personal->save($personal_data)) {
            $errors = $this->personal->errors();
            log_message('error', json_encode($errors));
            throw new Exception('Gagal menyimpan data identitas.');
        }

        return $personnel_data['id'];
    }

    public function postEdit($id)
    {
        if (!is_authorized($this->_menu_code, 'update')) {
            return access_denied();
        }

        $personal_rules = $this->personal->validationRules;
        unset(
            $personal_rules['place_of_birth'],
            $personal_rules['identity_address'],
            $personal_rules['address'],
            $personal_rules['identity_number'],
            $personal_rules['family_identity_number'],
            $personal_rules['photo_path'],
            $personal_rules['identity_card_path'],
            $personal_rules['province_id'],
            $personal_rules['city_id'],
            $personal_rules['district_id'],
            $personal_rules['village_id'],
            $personal_rules['religion_id'],
            $personal_rules['marital_status_id'],
            $personal_rules['employment_type_id'],
            $personal_rules['responsibility_letter_path']
        );

        $personal_messages = $this->personal->validationMessages;
        unset(
            $personal_messages['place_of_birth'],
            $personal_messages['identity_address'],
            $personal_messages['address'],
            $personal_messages['identity_number'],
            $personal_messages['family_identity_number'],
            $personal_messages['photo_path'],
            $personal_messages['identity_card_path'],
            $personal_messages['province_id'],
            $personal_messages['city_id'],
            $personal_messages['district_id'],
            $personal_messages['village_id'],
            $personal_messages['religion_id'],
            $personal_messages['marital_status_id'],
            $personal_messages['employment_type_id'],
            $personal_messages['responsibility_letter_path']
        );

        // Override Personal Model Validation Rules
        $this->personal->setValidationRules($personal_rules);
        $this->personal->setValidationMessages($personal_messages);

        $personnel_rules = $this->personnel->validationRules;
        unset(
            $personnel_rules['employment_type_id'],
            $personnel_rules['responsibility_letter_path']
        );

        $personnel_messages = $this->personnel->validationMessages;
        unset(
            $personnel_messages['employment_type_id'],
            $personnel_messages['responsibility_letter_path']
        );

        // Override Personnel Model Validation Rules
        $this->personnel->setValidationRules($personnel_rules);
        $this->personnel->setValidationMessages($personnel_messages);

        $validations = [
            'rules'   => array_merge(
                $personal_rules,
                $personnel_rules,
                $this->user->validationRules
            ),
            'message' => array_merge(
                $personal_messages,
                $personnel_messages,
                $this->user->validationMessages
            ),
        ];

        if (empty($user_data['password']) && empty($user_data['confirm_password'])) {
            unset($validations['rules']['password']);
            unset($validations['rules']['confirm_password']);
            unset($validations['message']['password']);
            unset($validations['message']['confirm_password']);
        }

        $this->message = 'Data berhasil diperbarui';

        return parent::_execute('this', 'personnel_identity_and_account_update', $this->_uri_name, ['id' => $id], $validations);
    }

    protected function personnel_identity_and_account_update($id)
    {
        $personal = $this->personal->select('id, acl_user_id')->where('employee_personnel_id', $id)->first();
        $user = $this->user->select('id')->where('id', $personal['acl_user_id'])->first();

        /*
        * Personnel Data
        */
        $personnel_data = [
            'employee_number'     => $this->request->getPost('employee_number'),
            'employment_id'       => $this->request->getPost('employment_id'),
            'assortment_id'       => $this->request->getPost('assortment_id'),
            'position_id'         => $this->request->getPost('position_id'),
            'organization_structure_id' => $this->request->getPost('organization_structure_id'),
        ];

        $this->_validate_unique_fields($this->personnel, $this->personnel->uniqueFields, $personnel_data, $id);

        // Save personnel data 
        if (!$this->personnel->update($id, $personnel_data)) {
            throw new Exception('Gagal memperbarui data kepegawaian.');
        }

        /*
        * User Data
        */
        $user_data = $this->_set_model_values($this->user, 'post');
        unset($user_data['id']);

        if (empty($user_data['password']) && empty($user_data['confirm_password'])) {
            unset($user_data['password']);
            unset($user_data['confirm_password']);
        }

        // Unique username validation
        try {
            $this->_validate_unique_username($this->user, $user_data['username'], $user['id']);
        } catch (Exception $e) {
            // If there is an unique error validation, return response error
            throw new Exception($e->getMessage());
        }

        $group_ids = $this->request->getPost('group_id[]');
        $group_names = empty($group_ids) ? [] : $this->group->select('group_name')->whereIn('id', $group_ids)->findAll();

        // Derive email & role from personal data and selected group access
        $user_data['email'] = $this->request->getPost('personal_email') ?: NULL;
        $user_data['role']  = empty($group_names) ? NULL : implode(', ', array_column($group_names, 'group_name'));

        // Save user data
        if (!$this->user->protect(false)->update($user['id'], $user_data)) {
            throw new Exception('Gagal memperbarui data user.');
        }

        // Assign user to group
        $this->user_lib->assign_user_groups($user['id'], $group_ids);

        /*
        * Personal Data
        */
        $personal_data = [
            'full_name'             => $this->request->getPost('full_name'),
            'personal_email'        => $this->request->getPost('personal_email'),
            'phone_number'          => $this->request->getPost('phone_number'),
            'wa_number'             => $this->request->getPost('wa_number'),
            'gender'                => $this->request->getPost('gender'),
            'date_of_birth'         => $this->request->getPost('date_of_birth'),
        ];

        $this->_validate_unique_fields($this->personal, $this->personal->uniqueFields, $personal_data, $personal['id']);

        // Save personal data 
        if (!$this->personal->update($personal['id'], $personal_data)) {
            throw new Exception('Gagal memperbarui data identitas.');
        }

        return $id;
    }

    public function postDelete($id)
    {
        if (!is_authorized($this->_menu_code, 'delete')) {
            return access_denied();
        }

        $this->message = 'Data berhasil dihapus';
        return parent::_execute('this', 'delete_personnel', $this->_uri_name, ['id' => $id]);
    }

    protected function delete_personnel($id)
    {
        try {
            $personal = $this->personal->select('acl_user_id')->where('employee_personnel_id', $id)->first();

            $this->user_group->where('user_id', $personal['acl_user_id'])->delete();
            $this->personal->where('employee_personnel_id', $id)->delete();
            $this->user->delete($personal['acl_user_id']);
            $this->personnel->delete($id);

            return $id;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getDownload($employee_number, $folder, $file_name)
    {
        $file_path = WRITEPATH . "uploads/dosier/$employee_number/personnel/$folder/$file_name";
        $mime = mime_content_type($file_path);

        if (!file_exists($file_path)) {
            return $this->response_error('File Tidak Ditemukan!');
        }

        return $this->response->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $file_name . '"')
            ->setBody(file_get_contents($file_path));
    }

    public function getContent($personnel_id, $content)
    {
        if ($this->request->isAJAX()) {
            $view_path = '';

            switch ($content) {
                case 'biodata':
                    $view_path = 'employee/personnel/biodata';
                    break;
                case 'employment':
                    $view_path = 'employee/personnel/employment';
                    break;
                case 'education':
                    $view_path = 'employee/personnel/education';
                    break;
                case 'publication':
                    $view_path = 'employee/personnel/publication';
                    break;
                default:
                    return $this->response->setJSON(['error' => 'Unknown content']);
            }

            return view($view_path, ['personnel_id' => $personnel_id]);
        }

        return $this->response->setJSON(['error' => 'Invalid Request']);
    }

    public function getShow_photo($employee_number, $file_name)
    {
        $path = WRITEPATH . 'uploads/dosier/' . $employee_number . '/personal/photo/' . $file_name;

        if (!file_exists($path)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setBody(file_get_contents($path));
    }
}
