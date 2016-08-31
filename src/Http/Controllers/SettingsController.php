<?php

namespace SystemInc\LaravelAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Http\Request;
use Storage;
use SystemInc\LaravelAdmin\Admin;
use SystemInc\LaravelAdmin\Setting;

class SettingsController extends Controller
{
    /**
     * Layouts controller index page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {
        $admins = Admin::all();

        $setting = Setting::first();

        return view('admin::settings.index', compact('admins', 'setting'));
    }

    /**
     * Update admin panel.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postUpdate(Request $request)
    {
        $file = $request->file('logo');

        if ($file && $file->isValid()) {
            $dirname = 'admin-panel/'.$file->getClientOriginalName();

            Storage::deleteDirectory('admin-panel');
            Storage::put($dirname, file_get_contents($file));

            $source = $dirname;
        }
        $data = [
            'title'  => !empty($request->title) ? $request->title : null,
            'source' => !empty($source) ? $source : null,
        ];

        $setting = Setting::first();

        if (!empty($setting->id)) {
            $setting->update($data);
        } else {
            Setting::create($data);
        }

        return back();
    }

    /**
     * Edit admin.
     *
     * @param int $admin_id
     *
     * @return \Illuminate\Http\Response
     */
    public function getEdit($admin_id)
    {
        $admin = Admin::find($admin_id);

        return view('admin::settings.edit', compact('admin'));
    }

    /**
     *  Change admin password.
     *
     * @return \Illuminate\Http\Response
     */
    public function postChangePassword(Request $request, $admin_id)
    {
        $admin = Admin::find($admin_id);

        if (Hash::check($request->old_pass, $admin->password)) {
            if ($request->new_pass === $request->confirm_pass) {
                $admin->password = Hash::make($request->new_pass);

                $admin->save();

                return back()->with(['success' => 'Password changed']);
            } else {
                return back()->with(['pass' => 'Wrong repeat password']);
            }
        } else {
            return back()->with(['pass' => 'Wrong old password']);
        }
    }

    /**
     * Update admin.
     *
     * @param Request $request
     * @param int     $admin_id
     *
     * @return \Illuminate\Http\Response
     */
    public function postUpdateAdmin(Request $request, $admin_id)
    {
        $admin = Admin::find($admin_id);

        if (empty($request->name) || empty($request->email)) {
            return back()->with(['error' => "Can't leave empty fields"]);
        }

        if (Admin::whereEmail($request->email)->first()) {
            return back()->with(['error' => "Admin with $request->email exist"]);
        }

        $admin->name = $request->name;
        $admin->email = $request->email;

        $admin->save();

        return back();
    }

    /**
     * Add admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAddAdmin()
    {
        return view('admin::settings.create');
    }

    /**
     * Save admin.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postCreateAdmin(Request $request)
    {
        if (empty($request->name) || empty($request->email) || empty($request->password)) {
            return back()->with(['error' => "Can't leave empty fields"]);
        }

        if (Admin::whereEmail($request->email)->first()) {
            return back()->with(['error' => "Admin with $request->email exist"]);
        }

        Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect($request->segment(1).'/settings');
    }
}
