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
    public function sendAccountNotification($data, $emailType, $userType, $link)
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

        if (env('MAIL_ENABLE') == 'true') {
            Mail::to($email)->send(new AuthenticateMail([
                'eType' => $emailType,
                'uType' => $userType,
                'name' => Str::headline($name),
                'date' => Carbon::now()->format('d F Y g:i A'),
                'link' => $link
            ]));
        }
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
                if ($student->student_status == 1) {
                    Auth::guard('student')->login($student);
                    $req->session()->regenerate();
                    return redirect()->route('student-home');
                } else {
                    return back()->withInput()->with('error', 'Your account is currently inactive. Please contact the system administrator for assistance.');
                }
            }

            // Attempt login as Staff
            $staff = Staff::where('staff_email', $req->email)->first();
            if ($staff && Hash::check($req->password, $staff->staff_password)) {
                if ($staff->staff_status == 1) {
                    Auth::guard('staff')->login($staff);
                    $req->session()->regenerate();
                    return redirect()->route('staff-dashboard');
                } else {
                    return back()->withInput()->with('error', 'Your account is currently inactive. Please contact the system administrator for assistance.');
                }
            }

            // If both fail
            return back()->withInput()->with('error', 'The email or password you entered is incorrect. Please try again.');
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
    public function staffDashboardV1()
    {
        try {

            // $studentBySemester = DB::table('student_semesters as a')
            //     ->join('semesters as b', 'a.semester_id', '=', 'b.id')
            //     ->select(
            //         'b.sem_label',
            //         'a.ss_status',
            //         DB::raw('COUNT(a.student_id) as total_students')
            //     )
            //     ->groupBy('b.sem_label', 'a.ss_status')
            //     ->orderBy('b.sem_label')
            //     ->get();

            // // Get latest 6 semesters with sem_status 1 or 3
            // $semesters = DB::table('semesters')
            //     ->whereIn('sem_status', [1, 3])
            //     ->orderBy('sem_startdate', 'desc')
            //     ->limit(6)
            //     ->get();

            // $currentSemester = DB::table('semesters')
            //     ->where('sem_status', 1) // active or open semester
            //     ->orderBy('sem_startdate', 'desc')
            //     ->first();

            // // Get student count by semester & programme (with mode)
            // $studentByProgrammeBySemester = DB::table('student_semesters as a')
            //     ->join('semesters as b', 'a.semester_id', '=', 'b.id')
            //     ->join('students as c', 'a.student_id', '=', 'c.id')
            //     ->join('programmes as d', 'c.programme_id', '=', 'd.id')
            //     ->select(
            //         'b.sem_label',
            //         'd.prog_code',
            //         'd.prog_mode',
            //         DB::raw('COUNT(a.student_id) as total_students')
            //     )
            //     ->whereIn('a.semester_id', $semesters->pluck('id'))
            //     ->groupBy('b.sem_label', 'd.prog_code', 'd.prog_mode')
            //     ->orderBy('b.sem_startdate', 'asc')
            //     ->get();


            // $unassignedStudentsCount = DB::table('students as a')
            //     ->join('student_semesters as ss', function ($join) use ($currentSemester) {
            //         $join->on('ss.student_id', '=', 'a.id')
            //             ->where('ss.semester_id', '=', $currentSemester->id);
            //     })
            //     ->leftJoin('supervisions as s', 's.student_id', '=', 'a.id')
            //     ->where('a.student_status', 1)
            //     ->select('a.id')
            //     ->groupBy('a.id')
            //     ->havingRaw('COUNT(s.staff_id) = 0')
            //     ->get()
            //     ->count();

            // $totalStudents = DB::table('students')->count();
            // $totalStaff = DB::table('staff')->count();
            // $totalProgrammes = DB::table('programmes')->count();

            /* SUPERVISOR DASHBOARD */

            /*-- SECTION : ACTION REQUIRED --*/

            /* 1.0 PENDING SUBMISSION APPROVAL [COUNT] */

            /* 2.0 PENDING NOMINATION [COUNT] */

            /* 3.0 PENDING EVALUATION BY ACTIVITY [COUNT] */

            /* 4.0 PENDING CORRECTION APPROVAL [COUNT] */


            /* CHAIR DASHBOARD */

            /*-- SECTION : ACTION REQUIRED --*/

            /* 1.0 PENDING EVALUATION BY ACTIVITY [COUNT] */


            /* EXAMINER/PANEL DASHBOARD */

            /*-- SECTION : ACTION REQUIRED --*/

            /* 1.0 PENDING EVALUATION BY ACTIVITY [COUNT] */

            /* 2.0 PENDING CORRECTION APPROVAL [COUNT] */


            return view('staff.auth.staff-dashboard', [
                'title' => 'Dashboard',
                // 'studentBySemester' => $studentBySemester,
                // 'studentByProgrammeBySemester' => $studentByProgrammeBySemester,
                // 'semesters' => $semesters,
                // 'totalStudents' => $totalStudents,
                // 'totalStaff' => $totalStaff,
                // 'totalProgrammes' => $totalProgrammes,
                // 'unassignedStudentsCount' => $unassignedStudentsCount,
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500);
        }
    }

    public function staffDashboard()
    {
        try {

            /* SUPERVISOR DASHBOARD */

            /* IDENTIFY WHETHER STAFF IS A SUPERVISOR IN THIS SEMESTER */
            $isSupervisor = DB::table('supervisions')
                ->where('staff_id', auth()->user()->id)
                ->whereExists(function ($query) {
                    $latestSemesterSub = DB::table('student_semesters')
                        ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                        ->groupBy('student_id');
                    $query
                        ->select(DB::raw(1))
                        ->from('students as s')
                        ->joinSub($latestSemesterSub, 'latest', function ($join) {
                            $join->on('latest.student_id', '=', 's.id');
                        })
                        ->join('semesters as sem', 'sem.id', '=', 'latest.latest_semester_id')
                        ->where('s.id', DB::raw('supervisions.student_id'))
                        ->where('sem.sem_status', 1);
                })
                ->exists();

            /*-- SECTION : ACTION REQUIRED --*/

            /* 1.0 PENDING SUBMISSION APPROVAL [COUNT] */
            $pendingSupervisorSubmission = DB::table('student_activities as a')
                ->join('supervisions as b', 'a.student_id', '=', 'b.student_id')
                ->where('b.staff_id', auth()->user()->id)
                ->where('a.sa_status', 1)
                ->count() ?? 0;

            /* 2.0 PENDING NOMINATION [COUNT] */
            $pendingSupervisorNomination = DB::table('nominations as a')
                ->join('activities as b', 'a.activity_id', '=', 'b.id')
                ->join('supervisions as c', 'a.student_id', '=', 'c.student_id')
                ->where('c.staff_id', auth()->user()->id)
                ->where('a.nom_status', 1)
                ->select('b.act_name as activity_name', DB::raw('COUNT(a.id) as total_pending'))
                ->groupBy('b.act_name')
                ->get();

            /* 3.0 PENDING EVALUATION BY ACTIVITY [COUNT] */
            $pendingSupervisorEvaluation = DB::table('evaluations as a')
                ->join('activities as b', 'a.activity_id', '=', 'b.id')
                ->join('supervisions as c', 'a.student_id', '=', 'c.student_id')
                ->where('c.staff_id', auth()->user()->id)
                ->where('a.evaluation_status', 9)
                ->select('b.act_name as activity_name', DB::raw('COUNT(a.id) as total_pending'))
                ->groupBy('b.act_name')
                ->get();

            /* 4.0 PENDING CORRECTION APPROVAL [COUNT] */
            $pendingSupervisorCorrection = DB::table('activity_corrections as a')
                ->join('activities as b', 'a.activity_id', '=', 'b.id')
                ->join('supervisions as c', 'a.student_id', '=', 'c.student_id')
                ->where('c.staff_id', auth()->user()->id)
                ->where('a.ac_status', 2)
                ->select('b.act_name as activity_name', DB::raw('COUNT(a.id) as total_pending'))
                ->groupBy('b.act_name')
                ->get();

                
            /* CHAIR DASHBOARD */

            /* IDENTIFY WHETHER STAFF IS A CHAIR IN THIS SEMESTER */

            /*-- SECTION : ACTION REQUIRED --*/

            /* 1.0 PENDING EVALUATION BY ACTIVITY [COUNT] */


            /* EXAMINER/PANEL DASHBOARD */

            /* IDENTIFY WHETHER STAFF IS A EXAMINER/PANEL IN THIS SEMESTER */

            /*-- SECTION : ACTION REQUIRED --*/

            /* 1.0 PENDING EVALUATION BY ACTIVITY [COUNT] */

            /* 2.0 PENDING CORRECTION APPROVAL [COUNT] */


            return view('staff.auth.staff-dashboard', [
                'title' => 'Dashboard',

                /* SUPERVISOR DASHBOARD */
                'isSupervisor' => $isSupervisor,
                'pendingSupervisorSubmission' => $pendingSupervisorSubmission,
                'pendingSupervisorNomination' => $pendingSupervisorNomination,
                'pendingSupervisorEvaluation' => $pendingSupervisorEvaluation,
                'pendingSupervisorCorrection' => $pendingSupervisorCorrection,

            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
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
            $document = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->join('documents as d', 'c.id', '=', 'd.activity_id')
                ->join('submissions as e', 'd.id', '=', 'e.document_id')
                ->where('b.id', auth()->user()->programme_id)
                ->where('e.student_id', auth()->user()->id)
                ->select(
                    'c.id as activity_id',
                    'c.act_name as activity_name',
                    'd.doc_name as document_name',
                    'd.isRequired',
                    'e.id as submission_id',
                    'e.submission_status',
                    'e.submission_duedate',
                    'e.submission_document',
                    'e.submission_date',
                )
                ->get();

            return view('student.auth.student-home', [
                'title' => 'Student Dashboard',
                'documents' => $document,
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    /* Student Profile [Student] - Route | Last Checked: 16-08-2025 */
    public function studentProfile()
    {
        try {
            /* RETURN VIEW */
            return view('student.auth.student-profile', [
                'title' => 'My Profile',
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* Update Student Profile [Student] - Function | Last Checked: 16-08-2025 */
    public function updateStudentProfile(Request $req)
    {
        /* KEEP THE TAB ACTIVE */
        session()->flash('active_tab', 'profile-1');

        /* VALIDATE STUDENT DATA */
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

        /* REDIRECT BACK IF VALIDATION FAILS */
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            /* GET VALIDATED DATA */
            $validated = $validator->validated();

            /* LOAD STUDENT DATA */
            $student = Student::where('id', Auth::guard('student')->user()->id)->first();

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            /* GET STUDENT NAME */
            $student_name = Str::upper($validated['student_name']);

            /* MAKE STUDENT DIRECTORY PATH */
            $oldDirectory = $student->student_directory;
            $validated['student_directory'] = "Student/" . $student->student_matricno . "_" . str_replace(' ', '_', $student_name);

            /* HANDLE STUDENT DIRECTORY */
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

            /* UPDATE STUDENT PROFILE */
            Student::where('id', $student->id)->update([
                'student_name' => Str::headline($validated['student_name']),
                'student_address' => $validated['student_address'] ?? null,
                'student_phoneno' => $validated['student_phoneno'] ?? null,
                'student_directory' => $validated['student_directory'] ?? null,
            ]);

            /* RETURN SUCCESS */
            return back()->with('success', 'Profile updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating profile: ' . $e->getMessage());
        }
    }

    /* Update Student Password [Student] - Function | Last Checked: 16-08-2025 */
    public function updateStudentPassword(Request $req)
    {
        /* KEEP THE TAB ACTIVE */
        session()->flash('active_tab', 'profile-2');

        /* VALIDATE PASSWORD */
        $validator = Validator::make($req->all(), [
            'oldPass' => 'required | min:8',
            'newPass' => 'required | min:8',
            'renewPass' => 'required | same:newPass',
        ], [], [
            'oldPass' => 'Old Password',
            'newPass' => 'New Password',
            'renewPass' => 'Comfirm Password',
        ]);

        /* REDIRECT BACK IF VALIDATION FAILS */
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            /* GET VALIDATED DATA */
            $validated = $validator->validated();

            /* CHECK OLD PASSWORD */
            $check = Hash::check($validated['oldPass'], Auth::guard('student')->user()->student_password, []);

            /* IF PASSWORD IS CORRECT */
            if ($check) {

                /* UPDATE PASSWORD */
                Student::where('id', Auth::guard('student')->user()->id)->update([
                    'student_password' => bcrypt($validated['renewPass'])
                ]);

                /* RETURN SUCCESS */
                return back()->with('success', 'Password has been updated successfully.');
            } else {

                /* RETURN ERROR */
                return back()->with('error', 'Please enter the correct password.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating password: ' . $e->getMessage());
        }
    }
}
