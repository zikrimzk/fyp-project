<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\AuthenticateMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthenticateController extends Controller
{

    /* General Function */
    private function sendAccountNotification($data, $emailType, $userType, $link)
    {
        //USER TYPE 
        if ($userType == 1) {
            $name = $data->student_name;
            $email = $data->student_email;
        } elseif ($userType == 2) {
            $name = $data->staff_name;
            $email = $data->staff_email;
        } else {
            $name = null;
            $email = null;
        }

        //EMAIL TYPE
        // 1 - ACCOUNT REGISTRATION
        // 2 - ACCOUNT DEACTIVATION
        // 3 - FORGOT PASSWORD
        // 4 - PASSWORD RESET NOTIFICATION

        Mail::to($email)->send(new AuthenticateMail([
            'eType' => $emailType,
            'uType' => $userType,
            'name' => Str::headline($name),
            'date' => Carbon::now()->format('d F Y g:i A'),
            'link' => $link
        ]));
    }

    public function mainLogin()
    {
        try {
            return view('auth.login', [
                'title' => 'Login',
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function authenticateUser(Request $req)
    {
        try {

            $req->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Attempt login as Student
            $student = Student::where('student_email', $req->email)->first();
            if ($student && Hash::check($req->password, $student->student_password)) {
                Auth::guard('student')->login($student);

                $req->session()->regenerate();
                return redirect()->route('student-home');
            }

            // Attempt login as Staff
            $staff = Staff::where('staff_email', $req->email)->first();

            if ($staff && Hash::check($req->password, $staff->staff_password)) {
                Auth::guard('staff')->login($staff);

                $req->session()->regenerate();
                return redirect()->route('staff-dashboard');
            }

            // If both fail
            return back()->withInput()->with('error', 'Invalid credentials. Please try again.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error authenticating user: ' . $e->getMessage());
        }
    }

    public function logoutUser()
    {
        try {

            if (Auth::guard('student')->check()) {
                Auth::guard('student')->logout();
            } elseif (Auth::guard('staff')->check()) {
                Auth::guard('staff')->logout();
            }
            return redirect('/')->with('success', 'Logged out successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error logout user: ' . $e->getMessage());
        }
    }

    /* Forgot Password Function */
    public function forgotPassword()
    {
        try {
            return view('auth.forgot-password', [
                'title' => 'Reset Password',
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function requestResetPassword(Request $req)
    {
        try {
            $req->validate([
                'email' => 'required|email',
            ]);

            $user = null;
            $userType = null;
            $email = $req->input('email');

            // Attempt to search in the Student table
            $student = Student::where('student_email', $req->email)->first();
            if ($student) {
                $user = $student;
                $userType = 1;
            }

            // Attempt to search in the Student table
            $staff = Staff::where('staff_email', $req->email)->first();

            if ($staff) {
                $user = $staff;
                $userType = 2;
            }

            if (!$user) {
                return back()->with('error', 'Email address not found in our records.');
            } else {
                // Generate a secure token
                $token = Str::random(64);

                // Insert token into password_resets table
                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $email],
                    [
                        'token' => $token,
                        'created_at' => Carbon::now()
                    ]
                );

                $resetLink = route('reset-password-form', ['token' => $token, 'email' => Crypt::encrypt($email), 'userType' => $userType]);

                $this->sendAccountNotification($user, 3, $userType, $resetLink);

                return back()->with('success', 'Password reset link sent successfully. Please check your email.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error requesting reset password: ' . $e->getMessage());
        }
    }

    public function resetPasswordForm($token, $email, $userType)
    {
        try {
            $email = Crypt::decrypt($email);

            return view('auth.reset-password-form', [
                'title' => 'Change Your Password',
                'token' => $token,
                'email' => $email,
                'userType' => $userType,
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function resetPassword(Request $req, $token, $email, $userType)
    {
        try {
            $email = Crypt::decrypt($email);

            $tokenData = DB::table('password_reset_tokens')
                ->where('token', $token)
                ->where('email', $email)
                ->first();

            $main_link = route('main-login');

            if ($userType == 1) {
                if (!$tokenData) {
                    return redirect($main_link)->with('error', 'Invalid or expired link.');
                }

                if (Carbon::parse($tokenData->created_at)->addHour()->isPast()) {
                    return redirect($main_link)->with('message', 'The link has expired.');
                }

                DB::table('students')->where('student_email', $email)->update([
                    'student_password' => bcrypt($req->input('renewPass'))
                ]);

                $user = Student::where('student_email', $email)->first();
            } elseif ($userType == 2) {
                if (!$tokenData) {
                    return redirect($main_link)->with('error', 'Invalid or expired link.');
                }

                if (Carbon::parse($tokenData->created_at)->addHour()->isPast()) {
                    return redirect($main_link)->with('message', 'The link has expired.');
                }

                DB::table('staff')->where('staff_email', $email)->update([
                    'staff_password' => bcrypt($req->input('renewPass'))
                ]);

                $user = Staff::where('staff_email', $email)->first();
            } else {
                return redirect($main_link)->with('error', 'Invalid user type.');
            }
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            $this->sendAccountNotification($user, 4, $userType, $main_link);

            return redirect($main_link)->with('success', 'Password has been reset successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error reseting password: ' . $e->getMessage());
        }
    }

    /* Staff Function */
    public function staffDashboard()
    {
        try {
            return view('staff.auth.staff-dashboard', [
                'title' => 'Dashboard',
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function staffProfile()
    {
        try {
            return view('staff.auth.staff-profile', [
                'title' => 'My Profile',
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function updateStaffProfile(Request $req)
    {
        session()->flash('active_tab', 'profile-1');

        $validator = Validator::make($req->all(), [
            'staff_name' => 'required|string|max:255',
            'staff_phoneno' => 'nullable|string|max:13',
            'staff_photo' => 'nullable|image|mimes:jpg,jpeg,png',
        ], [], [
            'staff_name' => 'staff name',
            'staff_phoneno' => 'staff phone number',
            'staff_photo' => 'staff photo',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validated = $validator->validated();
            $staff = Staff::where('id', Auth::guard('staff')->user()->id)->first() ?? null;


            /* MAKE STAFF DIRECTORY PATH */
            $staffDir = "Staff-Photo";

            /* SAVE OR RESET STAFF PHOTO */
            if ($req->input('remove_photo') == "1") {

                // 1 - REMOVE OLD PHOTO
                if ($staff->staff_photo && Storage::exists($staff->staff_photo)) {
                    Storage::delete($staff->staff_photo);
                }

                // 2 - SET TO NULL
                $staff->staff_photo = null;
                $staff->save();
            } elseif ($req->hasFile('staff_photo')) {

                // 1 - REMOVE OLD PHOTO
                if ($staff->staff_photo && Storage::exists($staff->staff_photo)) {
                    Storage::delete($staff->staff_photo);
                }

                // 2 - GET THE SPECIFIC DATA
                $staff_id = Str::upper($staff->staff_id);

                // 3 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($staff_id . '_' . time() . '_PHOTO') . '.' . $req->file('staff_photo')->getClientOriginalExtension();
                $filePath = $staffDir;

                // 4 - SAVE THE FILE
                $file = $req->file('staff_photo');
                $filePath = $file->storeAs($filePath, $fileName, 'public');

                // 5 - UPDATE PHOTO PATH
                $staff->staff_photo = $filePath;
                $staff->save();
            }

            /* UPDATE STAFF DATA */
            Staff::where('id', $staff->id)->update([
                'staff_name' => Str::headline($validated['staff_name']),
                'staff_phoneno' => $validated['staff_phoneno'] ?? null,
            ]);

            return back()->with('success', 'Profile updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating profile: ' . $e->getMessage());
        }
    }

    public function updateStaffPassword(Request $req)
    {
        session()->flash('active_tab', 'profile-2');

        $validator = Validator::make($req->all(), [
            'oldPass' => 'required | min:8',
            'newPass' => 'required | min:8',
            'renewPass' => 'required | same:newPass',
        ], [], [
            'oldPass' => 'Old Password',
            'newPass' => 'New Password',
            'renewPass' => 'Comfirm Password',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validated = $validator->validated();
            $check = Hash::check($validated['oldPass'], Auth::guard('staff')->user()->staff_password, []);
            if ($check) {
                Staff::where('id', Auth::guard('staff')->user()->id)->update(['staff_password' => bcrypt($validated['renewPass'])]);
                return back()->with('success', 'Password has been updated successfully.');
            } else {
                return back()->with('error', 'Please enter the correct password.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating password: ' . $e->getMessage());
        }
    }

    /* Student Function */
    public function studentHome()
    {
        try {
            return view('student.auth.student-home', [
                'title' => 'Home',
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function studentProfile()
    {
        try {
            return view('student.auth.student-profile', [
                'title' => 'My Profile',
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function updateStudentProfile(Request $req)
    {
        session()->flash('active_tab', 'profile-1');

        $validator = Validator::make($req->all(), [
            'student_name' => 'required|string',
            'student_address' => 'nullable|string',
            'student_phoneno' => 'nullable|string|max:13',
            'student_photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'student_directory' => 'nullable|string',
        ], [], [
            'student_name' => 'student name',
            'student_address' => 'student address',
            'student_phoneno' => 'student phone number',
            'student_photo' => 'student photo',
            'student_directory' => 'student directory',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validated = $validator->validated();
            $student = Student::where('id', Auth::guard('student')->user()->id)->first() ?? null;

            /* GET CURRENT SEMESTER */
            $curr_sem = Semester::where('id', $student->semester_id)->first();
            $curr_sem_label = $curr_sem ? $curr_sem->sem_label : null;


            /* GET STUDENT NAME */
            $student_name = Str::upper($validated['student_name']);

            /* MAKE STUDENT DIRECTORY PATH */
            $oldDirectory = $student->student_directory;
            $validated['student_directory'] = "Student/" . ($curr_sem_label ? str_replace('/', '', $curr_sem_label) : 'Unknown') .
                "/" . $student->student_matricno . "_" . str_replace(' ', '_', $student_name);

            if ($oldDirectory !== $validated['student_directory']) {
                Storage::move($oldDirectory, $validated['student_directory']);
            } else {
                Storage::makeDirectory($validated['student_directory']);
            }

            /* SAVE OR RESET STUDENT PHOTO */
            if ($req->input('remove_photo') == "1") {
                // 1 - REMOVE OLD PHOTO
                if (!empty($student->student_photo)) {
                    Storage::delete($student->student_directory . '/photo/' . $student->student_photo);
                }

                // 2 - SET TO NULL
                Student::where('id', $student->id)->update([
                    'student_photo' => null
                ]);
            } elseif ($req->hasFile('student_photo')) {
                // 1 - REMOVE OLD PHOTO
                if ($student->student_photo && Storage::exists($student->student_directory . '/photo/' . $student->student_photo)) {
                    Storage::delete($student->student_directory . '/photo/' . $student->student_photo);
                }

                // 2 - GET THE DATA
                $student_matricno = Str::upper($student->student_matricno);

                // 3 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($student_matricno . '_' . time() . '_PHOTO') . '.' . $req->file('student_photo')->getClientOriginalExtension();
                $filePath = $validated['student_directory'] . "/photo";

                // 4 - SAVE THE FILE
                $file = $req->file('student_photo');
                $filePath = $file->storeAs($filePath, $fileName, 'public');

                Student::where('id', $student->id)->update([
                    'student_photo' => $fileName
                ]);
            }

            Student::where('id', $student->id)->update([
                'student_name' => Str::headline($validated['student_name']),
                'student_address' => $validated['student_address'] ?? null,
                'student_phoneno' => $validated['student_phoneno'] ?? null,
                'student_directory' => $validated['student_directory'] ?? null,
            ]);

            return back()->with('success', 'Profile updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating profile: ' . $e->getMessage());
        }
    }

    public function updateStudentPassword(Request $req)
    {
        session()->flash('active_tab', 'profile-2');

        $validator = Validator::make($req->all(), [
            'oldPass' => 'required | min:8',
            'newPass' => 'required | min:8',
            'renewPass' => 'required | same:newPass',
        ], [], [
            'oldPass' => 'Old Password',
            'newPass' => 'New Password',
            'renewPass' => 'Comfirm Password',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validated = $validator->validated();
            $check = Hash::check($validated['oldPass'], Auth::guard('student')->user()->student_password, []);
            if ($check) {
                Student::where('id', Auth::guard('student')->user()->id)->update(['student_password' => bcrypt($validated['renewPass'])]);
                return back()->with('success', 'Password has been updated successfully.');
            } else {
                return back()->with('error', 'Please enter the correct password.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating password: ' . $e->getMessage());
        }
    }
}
