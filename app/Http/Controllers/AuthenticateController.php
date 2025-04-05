<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthenticateController extends Controller
{

    /* General Function */
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
}
