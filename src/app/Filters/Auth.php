<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session()->get('login_user_id');
        $is_change_password = session()->get('is_change_password');

        $sso_login = session()->get('sso_login');

        if ($request->uri->getSegment(1) === 'authentication' && $request->uri->getSegment(2) === 'sso') {
            return $request;
        }

        if (!empty($sso_login)) {
            if (!is_valid_token($sso_login['id_token'])) {
                if (refresh_token($sso_login['refresh_token'])) {
                    return redirect()->to(current_url());
                }

                session()->destroy();

                return redirect()->to('authentication/login');
            } else {
                return $request;
            }
        }

        if (!$user) {
            return redirect()->to('authentication/login');
        }

        // Redirect if is change password is true
        if (!empty($is_change_password)) {
            return redirect()->to('authentication/change_password');
        }

        // $blocked_url = ['acl/users', 'acl/users/form'];

        // if (empty(session()->get('login_personnel_id'))) {
        //     return $request;
        // } else {
        //     if (in_array($request->uri->getPath(), $blocked_url)) {
        //         return $request;
        //     } else {
        //         return access_denied();
        //     }
        // }

        // if (in_array('Verifikator', session()->get('login_user_group'))) {
        // if ($request->uri->getSegment(3) === 'verification') {
        //     return $request;
        // } else {
        //     return redirect()->to(site_url('home/dashboard'))->with('error', 'You are not authorized to access this page.');
        // }
        // }

        // Role-based access control
        // switch ($user['role']) {
        //     case 'Super Admin':
        //         return $request;

        //     case 'Pengguna':
        //         if ($request->uri->getSegment(1) === 'assessment' && $request->uri->getSegment(2) === 'result' || $request->uri->getSegment(1) === 'auth' || $request->uri->getSegment(1) === 'profile') {
        //             return $request;
        //         } else {
        //             return redirect()->back()->with('error', 'You are not authorized to access this page.');
        //         }

        //     default:
        //         session()->setFlashdata('error', 'Invalid role. Please contact the administrator.');
        //         session()->destroy();

        //         return redirect()->to('authentication/login');
        // }
    }


    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
