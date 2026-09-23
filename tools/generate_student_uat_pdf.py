from pathlib import Path
from xml.sax.saxutils import escape

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfbase import pdfmetrics
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    Image,
    KeepTogether,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "output" / "pdf" / "e-Pasca_Student_UAT_Checklist.pdf"
LOGO = ROOT / "public" / "assets" / "images" / "logo-utem.PNG"

PAGE_W, PAGE_H = A4
NAVY = colors.HexColor("#17365D")
BLUE = colors.HexColor("#24588A")
TEAL = colors.HexColor("#137C8B")
PALE_BLUE = colors.HexColor("#EAF2F8")
PALE_GREY = colors.HexColor("#F5F7F9")
MID_GREY = colors.HexColor("#D7DEE5")
TEXT = colors.HexColor("#263238")
MUTED = colors.HexColor("#5F6B76")
GREEN = colors.HexColor("#2E7D32")
RED = colors.HexColor("#C62828")
AMBER = colors.HexColor("#9A6700")


def register_fonts():
    candidates = [
        ("C:/Windows/Fonts/arial.ttf", "UAT-Regular"),
        ("C:/Windows/Fonts/arialbd.ttf", "UAT-Bold"),
    ]
    for path, name in candidates:
        if Path(path).exists():
            pdfmetrics.registerFont(TTFont(name, path))
    return (
        "UAT-Regular" if "UAT-Regular" in pdfmetrics.getRegisteredFontNames() else "Helvetica",
        "UAT-Bold" if "UAT-Bold" in pdfmetrics.getRegisteredFontNames() else "Helvetica-Bold",
    )


REGULAR, BOLD = register_fonts()


styles = getSampleStyleSheet()
styles.add(ParagraphStyle(
    name="CoverTitle", fontName=BOLD, fontSize=22, leading=27, textColor=NAVY,
    alignment=TA_CENTER, spaceAfter=6,
))
styles.add(ParagraphStyle(
    name="CoverSub", fontName=REGULAR, fontSize=11, leading=15, textColor=MUTED,
    alignment=TA_CENTER,
))
styles.add(ParagraphStyle(
    name="Section", fontName=BOLD, fontSize=12, leading=15, textColor=colors.white,
    backColor=NAVY, borderPadding=(7, 9, 7, 9), spaceBefore=7, spaceAfter=7,
    keepWithNext=True,
))
styles.add(ParagraphStyle(
    name="BodySmall", fontName=REGULAR, fontSize=8, leading=10.5, textColor=TEXT,
))
styles.add(ParagraphStyle(
    name="Body", fontName=REGULAR, fontSize=9, leading=12, textColor=TEXT,
))
styles.add(ParagraphStyle(
    name="BoldSmall", fontName=BOLD, fontSize=8, leading=10, textColor=TEXT,
))
styles.add(ParagraphStyle(
    name="Tiny", fontName=REGULAR, fontSize=7, leading=8.6, textColor=MUTED,
))
styles.add(ParagraphStyle(
    name="TableHead", fontName=BOLD, fontSize=7.5, leading=9, textColor=colors.white,
    alignment=TA_CENTER,
))
styles.add(ParagraphStyle(
    name="TableCell", fontName=REGULAR, fontSize=7.4, leading=9.2, textColor=TEXT,
))
styles.add(ParagraphStyle(
    name="TableCellCenter", fontName=REGULAR, fontSize=7.2, leading=9, textColor=TEXT,
    alignment=TA_CENTER,
))
styles.add(ParagraphStyle(
    name="IssueTitle", fontName=BOLD, fontSize=8.2, leading=10, textColor=NAVY,
))


def P(text, style="Body"):
    return Paragraph(escape(str(text)).replace("\n", "<br/>"), styles[style])


def rich(text, style="Body"):
    return Paragraph(text, styles[style])


def line_field(label, width=125 * mm):
    return Table(
        [[P(label, "BoldSmall"), ""]], colWidths=[43 * mm, width], rowHeights=[9 * mm],
        style=TableStyle([
            ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
            ("LINEBELOW", (1, 0), (1, 0), 0.6, MUTED),
            ("LEFTPADDING", (0, 0), (-1, -1), 0),
            ("RIGHTPADDING", (0, 0), (-1, -1), 2),
        ])
    )


def test_case(test_id, title, action, expected):
    return {
        "id": test_id,
        "title": title,
        "action": action,
        "expected": expected,
    }


SECTIONS = [
    ("A. Authentication and password recovery", [
        test_case("STU-A01", "Open login page", "Navigate to the e-Pasca root URL.", "UTeM logo, e-Pasca title, email and password fields, Remember me, Forgot Password and Sign in are displayed without layout errors."),
        test_case("STU-A02", "Required login fields", "Submit the login form with email and/or password empty.", "Browser/system prevents submission and clearly identifies the required field(s)."),
        test_case("STU-A03", "Email format validation", "Enter an invalid email format and attempt to sign in.", "Invalid email is rejected; the form remains usable and no session is created."),
        test_case("STU-A04", "Password visibility toggle", "Enter a password and use the eye icon twice.", "Password changes from masked to visible and back without changing its value."),
        test_case("STU-A05", "Invalid student credentials", "Sign in using a valid-format email with an incorrect password.", "A clear error states that the email or password is incorrect; email remains available for correction."),
        test_case("STU-A06", "Inactive student account", "Attempt sign-in with a student account marked inactive.", "Access is refused and the user is told to contact the system administrator."),
        test_case("STU-A07", "Successful student sign-in", "Sign in with an active student email and correct password.", "User is authenticated, redirected to Student Dashboard, and sees the correct student identity."),
        test_case("STU-A08", "Remember me option", "Select Remember me, sign in, close and reopen the browser according to the test environment policy.", "The session persistence behaviour matches the configured policy and does not expose credentials."),
        test_case("STU-A09", "Open Forgot Password", "Select Forgot Password from login.", "Reset Password page opens with email field, Request Password Reset and Back to Login."),
        test_case("STU-A10", "Unknown reset email", "Submit an email not registered to a student or staff account.", "System reports that the email address was not found and does not disclose sensitive data."),
        test_case("STU-A11", "Request student reset link", "Submit the registered student email.", "Success message is shown and a reset email/link is delivered to the student."),
        test_case("STU-A12", "Reset-link validity", "Open the reset link within one hour, then separately test an invalid or expired link.", "Valid link opens the reset form; invalid/expired link is rejected and returns the user to login with a clear message."),
        test_case("STU-A13", "Reset password rules", "Enter passwords that fail and then satisfy 8+ characters, lowercase, uppercase, number and special-character indicators.", "Rule indicators update correctly; confirm-password and Reset Password become usable only when values satisfy the UI rules and match."),
        test_case("STU-A14", "Complete password reset", "Submit a valid matching new password, then sign in with it.", "Password reset succeeds, token cannot be reused, old password fails, and new password signs in successfully."),
    ]),
    ("B. Student navigation, header and session", [
        test_case("STU-B01", "Sidebar identity summary", "After login, inspect the sidebar header.", "Correct student name, profile image/default image, programme name/code, study mode and current semester are shown."),
        test_case("STU-B02", "Dashboard navigation", "Select Dashboard/Home from the sidebar or UTeM/e-Pasca brand.", "Student Dashboard opens and the active navigation state is visible."),
        test_case("STU-B03", "Programme Overview navigation", "Select Programme Overview.", "Programme Overview opens and the sidebar item is marked active, including while a document submission page is open."),
        test_case("STU-B04", "Journal Publication navigation", "Select Journal Publication.", "Journal Publication page opens and the correct sidebar item is marked active."),
        test_case("STU-B05", "Collapse and mobile navigation", "Use the desktop collapse control and mobile menu control.", "Navigation opens/closes correctly, remains readable and does not cover essential page actions."),
        test_case("STU-B06", "Account menu and My Profile", "Open the user menu in the header and select My Profile.", "Menu opens, shows the student name/photo, and My Profile loads."),
        test_case("STU-B07", "Logout", "Select Logout from the user menu.", "Session is invalidated, success message appears on login, and browser Back cannot reopen protected student pages."),
    ]),
    ("C. Student dashboard", [
        test_case("STU-C01", "Student welcome and current month", "Open Student Dashboard.", "Welcome message shows the authenticated student's name and the current month/year."),
        test_case("STU-C02", "Research title display", "Inspect Research Title with a populated and an empty title.", "Saved title is displayed; an empty title is represented safely (for example, a dash)."),
        test_case("STU-C03", "Edit research title", "Open Edit, enter a valid title up to 150 characters, and save.", "Modal opens; title is saved, normalized for display, and success feedback appears."),
        test_case("STU-C04", "Research title validation/cancel", "Try a title over 150 characters, then cancel/reset a valid unsaved edit.", "Overlength value is rejected with validation feedback; cancelled/reset changes are not saved."),
        test_case("STU-C05", "Supervisor details", "Inspect Supervisor information and select the email link.", "Assigned supervisor name/email are correct and mail link targets that address; unassigned state shows Not assigned."),
        test_case("STU-C06", "Co-supervisor details", "Inspect Co-Supervisor information and select the email link.", "Assigned co-supervisor name/email are correct and mail link targets that address; unassigned state shows Not assigned."),
        test_case("STU-C07", "Upcoming reminders", "Open Upcoming reminders with future-dated submissions.", "Count and rows match eligible submissions; each shows document, activity, required/optional badge, due date and relative deadline."),
        test_case("STU-C08", "Due-today/tomorrow/seven-day labels", "Use submissions due today, tomorrow, within seven days and later.", "Deadline wording and warning styles are correct for each boundary."),
        test_case("STU-C09", "Overdue reminders", "Open Overdue reminders with past-due actionable submissions.", "Overdue tab is selected when applicable; count, alert and 'Overdue by N days' labels are accurate."),
        test_case("STU-C10", "No-reminder empty states", "Use a student with no upcoming and no overdue actionable submissions.", "No urgent action message and appropriate empty states are displayed."),
        test_case("STU-C11", "Reminder deep link", "Select an upcoming and an overdue reminder.", "Correct Document Submission page opens for the selected submission."),
        test_case("STU-C12", "Nomination/evaluator details", "Inspect nomination details for assigned Examiner, Panel Member and Chairman records.", "Records are grouped by activity and show correct role, staff name and email link."),
        test_case("STU-C13", "Nomination empty/completed state", "Use an activity with no approved evaluators or one already completed.", "A clear no-details message is shown and completed activity nominations are not incorrectly listed."),
    ]),
    ("D. My Profile and change password", [
        test_case("STU-D01", "Profile tabs", "Open My Profile and switch between Personal Details and Change Password.", "Both tabs load correctly and the selected tab remains active after its form returns validation/success feedback."),
        test_case("STU-D02", "Read-only identity fields", "Inspect and try to change Email, Matric No and Programme.", "Correct values are shown and cannot be edited through the form."),
        test_case("STU-D03", "Update name, phone and address", "Edit student name, phone number and address with valid values, then save.", "Values persist after refresh, name is presented consistently, and success feedback appears."),
        test_case("STU-D04", "Required name validation", "Clear Student Name and save.", "Update is rejected, the field is identified, and other entered values remain available."),
        test_case("STU-D05", "Phone validation", "Enter nonconforming/overlength phone data and then a valid number.", "Invalid value is rejected according to the form/server limit; valid value saves with the +60 prefix presentation."),
        test_case("STU-D06", "Upload profile photo", "Choose a JPG, JPEG or PNG image and save.", "Preview updates; saved photo appears on profile, header and sidebar after refresh."),
        test_case("STU-D07", "Reject invalid profile photo", "Choose a non-image or unsupported image format.", "File is rejected with clear validation feedback and existing photo remains unchanged."),
        test_case("STU-D08", "Reset profile photo", "Select Reset Photo and save.", "Photo is removed and the default profile image appears consistently."),
        test_case("STU-D09", "Password visibility controls", "Use eye controls for old, new and confirm password fields.", "Each field independently toggles visible/masked without altering its content."),
        test_case("STU-D10", "New-password rule indicators", "Enter values that progressively meet length, lowercase, uppercase, number and special-character rules.", "Each indicator changes accurately and confirm/submit behaviour follows the displayed rules."),
        test_case("STU-D11", "Wrong old password", "Enter an incorrect old password with a valid matching new password.", "Password is not changed and a clear 'correct password' error is shown."),
        test_case("STU-D12", "Mismatched/short new password", "Use fewer than 8 characters or nonmatching confirmation.", "Submission is blocked or rejected with specific validation feedback."),
        test_case("STU-D13", "Successful password change", "Enter the correct old password and a valid matching new password, then save and re-login.", "Success feedback appears; old password fails and new password succeeds."),
    ]),
    ("E. Programme Overview and academic records", [
        test_case("STU-E01", "Inactive-semester notice", "Open Programme Overview as a student not active in the current semester.", "Enrollment Notice is displayed with guidance and Back to Home; activity submission controls are not exposed."),
        test_case("STU-E02", "Activity order and identity", "Open Programme Overview as an active student.", "All configured programme activities appear in configured sequence with correct names."),
        test_case("STU-E03", "Activity status labels", "Verify test data covering open, locked, pending supervisor, pending committee, approved, rejected, evaluation, correction, failed and continue-next-semester states.", "Each underlying activity state displays the correct human-readable badge and colour category."),
        test_case("STU-E04", "Journal prompt/action availability", "Inspect an activity configured for journal publication in eligible open/rejected/correction states.", "Optional journal guidance and/or Manage Journal Publication action appears only when intended."),
        test_case("STU-E05", "Download flowchart/material", "Select Download for an activity with material.", "Correct stored material opens/downloads in a new tab; signed link works and content is readable."),
        test_case("STU-E06", "No material state", "Inspect an activity without a material file.", "'No material uploaded' is displayed without a broken link."),
        test_case("STU-E07", "View approved final document", "Select View Final Document for an activity with a confirmed final submission.", "Correct PDF opens for the student, activity and semester."),
        test_case("STU-E08", "View final correction document", "Select View Final Document (Correction) where available.", "Correct correction PDF opens and is not confused with the original final document."),
        test_case("STU-E09", "View evaluation reports", "Open each listed evaluation report across available semesters.", "Report filename and semester label are correct; each PDF opens successfully."),
        test_case("STU-E10", "Review modal with comments", "Select View Review for an activity containing reviews.", "Modal lists the correct reviewer, date/time, relative time and full comment text."),
        test_case("STU-E11", "Review modal empty state", "Open View Review for an activity with no comments.", "Modal displays 'No reviews found' without an error."),
        test_case("STU-E12", "Document cards and semester labels", "Inspect documents for repeatable and non-repeatable activities.", "Document name, current/relevant semester (repeatable only), required/optional badge, status, due date and deadline are correct."),
        test_case("STU-E13", "Submission status variants", "Verify No Attempt, Submitted, Overdue, Locked and prohibited/other document states.", "Correct badge and available/disabled action are shown for every status."),
        test_case("STU-E14", "Submission actions by activity state", "Compare document actions while activity is open/rejected/correction versus pending/approved.", "Upload/View Submission is available only in permitted activity states; pending/approved states are locked."),
        test_case("STU-E15", "No document / no activity empty states", "Use an activity with no available documents and a programme with no activities.", "The relevant informative empty-state message is displayed."),
    ]),
    ("F. Document submission", [
        test_case("STU-F01", "Open correct submission", "From Programme Overview or Dashboard, open a document.", "Page title/activity/document are correct and Back navigation returns to Programme Overview."),
        test_case("STU-F02", "No-attempt/overdue summary", "Open a future-due unsubmitted document and a past-due unsubmitted document.", "Status, exact due date and relative deadline are correct; overdue page includes an urgent warning."),
        test_case("STU-F03", "Submitted summary", "Open an already submitted document.", "Submission date, before/after deadline timing, filename link, Update and Remove controls are shown."),
        test_case("STU-F04", "Open submitted PDF", "Select the submitted filename.", "Correct student-owned PDF opens in a new tab and is readable."),
        test_case("STU-F05", "Open/cancel upload area", "Select Submit Document, then Cancel.", "Upload area appears; Cancel clears selected files/preview and returns to the status summary without saving."),
        test_case("STU-F06", "No-file validation", "Open upload area and select Save Changes without choosing a file.", "User is told to select a document; no submission is created."),
        test_case("STU-F07", "File type validation", "Try to add a non-PDF file.", "Uploader/server rejects the file and explains that only PDF is allowed."),
        test_case("STU-F08", "File count validation", "Try to add more than one PDF.", "Only one file is accepted and the user receives clear feedback."),
        test_case("STU-F09", "File size validation", "Try PDFs below and above 100 MB.", "File up to the limit is accepted; over-limit file is rejected before or during submission with clear feedback."),
        test_case("STU-F10", "PDF preview", "Add a valid PDF on desktop-size viewport, then remove it from the uploader.", "Preview shows the selected PDF; removing it clears/hides the preview."),
        test_case("STU-F11", "Submit valid PDF", "Add one valid PDF and select Save Changes.", "Saving/progress state appears, page reloads, status becomes Submitted, date is recorded, and file uses the system naming/location convention."),
        test_case("STU-F12", "Update submission", "Select Update Submission, replace the existing PDF and save.", "Existing file loads into the uploader where supported; replacement succeeds and summary/file link reflect the new upload."),
        test_case("STU-F13", "Remove submission - cancel", "Select Remove Submission, then Cancel in the confirmation dialog.", "Dialog closes and the submission/file remain unchanged."),
        test_case("STU-F14", "Remove submission - confirm", "Confirm removal of an existing submission.", "File is removed, success message appears, submission date clears, and status returns to No Attempt or Overdue based on deadline."),
        test_case("STU-F15", "Upload failure recovery", "Simulate validation/server/network failure during Save Changes.", "Specific validation or general retry message appears; button re-enables and the user can retry without duplicate records."),
    ]),
    ("G. Final submission, correction and reviews", [
        test_case("STU-G01", "Final-confirm eligibility - required documents", "Complete all required documents for an eligible activity.", "Confirm Submission appears only after every required document is submitted and disallowed statuses do not qualify."),
        test_case("STU-G02", "Final-confirm eligibility - optional-only activity", "For an activity with no required documents, submit at least one optional document.", "Confirm Submission becomes available as designed; zero optional submissions do not qualify."),
        test_case("STU-G03", "Confirmation guidance", "Open Confirm Submission.", "Guidelines explain final review, original work, legal validity, locking and approval/revision outcome."),
        test_case("STU-G04", "Signature required and clear", "Try Confirm & Sign with an empty pad; draw a signature, select Start over, and try again.", "Empty signature is blocked; Start over clears the pad; a new signature can be drawn."),
        test_case("STU-G05", "Confirm and sign final submission", "Draw a signature and select Confirm & Sign once.", "Button prevents duplicate submission, signed form is generated, activity moves to pending supervisor approval, documents lock, and success message appears."),
        test_case("STU-G06", "Supervisor notification", "Complete final confirmation where a main supervisor is assigned.", "Supervisor receives the intended email with correct student/activity/submission details; absence of a supervisor does not crash the student flow."),
        test_case("STU-G07", "Correction confirmation availability", "Use Minor/Major Correction or correction-rejected activity states.", "Confirm Correction appears only in the intended correction states, with journal action where configured."),
        test_case("STU-G08", "Confirm correction with signature", "Open correction modal, verify signature validation/clear, then sign and confirm.", "Correction is generated and recorded, activity moves to pending supervisor correction approval, and success feedback appears."),
        test_case("STU-G09", "Post-confirmation locking", "Reopen Programme Overview after final or correction confirmation.", "Relevant upload/confirmation controls are no longer available until a workflow state permits revision."),
    ]),
    ("H. Journal Publication management", [
        test_case("STU-H01", "Load journal list", "Open Journal Publication.", "Only the authenticated student's records load with row number, journal name, Scopus/ISI indicator, created date and actions."),
        test_case("STU-H02", "Table interaction", "Use available search, sorting, pagination and responsive table controls with multiple records.", "Displayed results and ordering are accurate; controls remain usable on smaller screens."),
        test_case("STU-H03", "Open/close Add modal", "Select Add Journal Publication, then Cancel/close.", "Modal opens with clean fields and closes without creating a record."),
        test_case("STU-H04", "Add validation", "Submit Add with blank Journal Name and/or no Scopus/ISI selection.", "Inline validation identifies missing required values and no record is added."),
        test_case("STU-H05", "Add journal publication", "Enter a journal name, choose Yes or No for Scopus/ISI, and submit.", "Success toast appears, modal resets/closes, and new record appears without a full-page error."),
        test_case("STU-H06", "Open Edit modal", "Select Edit for a row.", "Correct journal ID, name and Scopus/ISI value are loaded into the modal."),
        test_case("STU-H07", "Update journal publication", "Change name and/or Scopus/ISI and submit.", "Validation works; success toast appears and the same row refreshes with saved values."),
        test_case("STU-H08", "Delete - cancel", "Select Delete, then Cancel.", "Warning dialog closes and record remains."),
        test_case("STU-H09", "Delete - confirm", "Select Delete and Delete Anyway.", "Success toast appears and the selected record is removed from the table."),
        test_case("STU-H10", "Journal error recovery", "Simulate add/update/delete failure.", "Error toast is shown, action button re-enables, modal/table remain usable, and data is not falsely changed."),
    ]),
    ("I. Security, access control and usability", [
        test_case("STU-I01", "Unauthenticated route protection", "Sign out and directly open each /student page URL.", "User is redirected to login and protected student content is not disclosed."),
        test_case("STU-I02", "Cross-student submission access", "As Student A, attempt to open or mutate Student B's submission using a copied/modified identifier.", "Access is denied; Student B's file and record are unchanged."),
        test_case("STU-I03", "Cross-student journal isolation", "Attempt to view/update/delete another student's journal record by altering request data.", "Other student's records are not exposed or modified; request is rejected/logged as appropriate."),
        test_case("STU-I04", "Invalid/missing files", "Open a link whose material/final/submission file is missing or invalid.", "System returns a controlled not-found/error response and no server path or sensitive details are exposed."),
        test_case("STU-I05", "Duplicate-submit protection", "Double-click Save/Confirm/Add/Update/Delete actions or retry during loading.", "Buttons disable while processing and only one intended record/action is created."),
        test_case("STU-I06", "Responsive layout", "Test login, dashboard, profile, programme, submission and journal pages at desktop, tablet and phone widths.", "Content remains readable; tables/cards wrap or scroll; modals, navigation and primary actions stay accessible."),
        test_case("STU-I07", "Keyboard and focus usability", "Navigate key pages, menus, tabs, modals and forms using keyboard only.", "Focus is visible and logical; controls can be operated; modal focus returns appropriately."),
        test_case("STU-I08", "Messages and data persistence", "Trigger success, validation and server-error responses across student functions, then refresh relevant pages.", "Messages are clear/dismissible, no duplicate action occurs on refresh, and successfully saved data persists."),
    ]),
]


TOTAL_CASES = sum(len(cases) for _, cases in SECTIONS)


class UATDocTemplate(BaseDocTemplate):
    def __init__(self, filename):
        super().__init__(
            filename,
            pagesize=A4,
            rightMargin=14 * mm,
            leftMargin=14 * mm,
            topMargin=21 * mm,
            bottomMargin=17 * mm,
            title="e-Pasca Student User Acceptance Testing Checklist",
            author="Universiti Teknikal Malaysia Melaka",
            subject="Internal UAT checklist for the Student role",
        )
        frame = Frame(self.leftMargin, self.bottomMargin, self.width, self.height, id="body")
        self.addPageTemplates([PageTemplate(id="main", frames=[frame], onPage=draw_page)])


def draw_page(canvas, doc):
    canvas.saveState()
    page = canvas.getPageNumber()
    if page > 1:
        canvas.setStrokeColor(MID_GREY)
        canvas.setLineWidth(0.5)
        canvas.line(14 * mm, PAGE_H - 14 * mm, PAGE_W - 14 * mm, PAGE_H - 14 * mm)
        if LOGO.exists():
            canvas.drawImage(str(LOGO), 14 * mm, PAGE_H - 12.6 * mm, width=22 * mm, height=9.2 * mm,
                             preserveAspectRatio=True, anchor="sw", mask="auto")
        canvas.setFont(BOLD, 8)
        canvas.setFillColor(NAVY)
        canvas.drawRightString(PAGE_W - 14 * mm, PAGE_H - 9.5 * mm, "e-Pasca | Student UAT Checklist")
    canvas.setStrokeColor(MID_GREY)
    canvas.line(14 * mm, 12 * mm, PAGE_W - 14 * mm, 12 * mm)
    canvas.setFont(REGULAR, 7)
    canvas.setFillColor(MUTED)
    canvas.drawString(14 * mm, 8 * mm, "Internal Use | UAT evidence should be retained with the completed form")
    canvas.drawRightString(PAGE_W - 14 * mm, 8 * mm, f"Page {page}")
    canvas.restoreState()


def cover_page(story):
    story.append(Spacer(1, 10 * mm))
    if LOGO.exists():
        img = Image(str(LOGO), width=52 * mm, height=23 * mm)
        img.hAlign = "CENTER"
        story.append(img)
    story.append(Spacer(1, 9 * mm))
    story.append(P("e-Pasca", "CoverTitle"))
    story.append(P("STUDENT USER ACCEPTANCE TESTING (UAT) CHECKLIST", "CoverTitle"))
    story.append(P("Universiti Teknikal Malaysia Melaka | Internal Use", "CoverSub"))
    story.append(Spacer(1, 12 * mm))

    info = Table([
        [P("Tester Name", "BoldSmall"), "", P("Matric Number", "BoldSmall"), ""],
        [P("Programme", "BoldSmall"), "", P("Study Mode", "BoldSmall"), "[  ] Full Time   [  ] Part Time"],
        [P("Test Date", "BoldSmall"), "", P("Environment / URL", "BoldSmall"), ""],
        [P("Browser / Device", "BoldSmall"), "", P("Build / Version", "BoldSmall"), ""],
    ], colWidths=[29 * mm, 58 * mm, 31 * mm, 61 * mm], rowHeights=[12 * mm] * 4)
    info.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.6, MID_GREY),
        ("BACKGROUND", (0, 0), (0, -1), PALE_BLUE),
        ("BACKGROUND", (2, 0), (2, -1), PALE_BLUE),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("FONTNAME", (1, 0), (-1, -1), REGULAR),
        ("FONTSIZE", (1, 0), (-1, -1), 8),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
    ]))
    story.append(info)
    story.append(Spacer(1, 9 * mm))

    purpose = Table([[rich(
        f"<b>Purpose.</b> This checklist validates the complete Student role identified in the current e-Pasca implementation. "
        f"It contains <b>{TOTAL_CASES} test cases</b>. Execute every applicable case and record evidence for each failure.",
        "Body")]], colWidths=[179 * mm])
    purpose.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), PALE_BLUE),
        ("BOX", (0, 0), (-1, -1), 0.8, BLUE),
        ("LEFTPADDING", (0, 0), (-1, -1), 9),
        ("RIGHTPADDING", (0, 0), (-1, -1), 9),
        ("TOPPADDING", (0, 0), (-1, -1), 8),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 8),
    ]))
    story.append(purpose)
    story.append(Spacer(1, 7 * mm))
    story.append(P("Result legend", "BoldSmall"))
    legend = Table([
        [P("[  ] PASS", "BoldSmall"), P("Observed result matches the expected result.", "BodySmall")],
        [P("[  ] FAIL", "BoldSmall"), P("Function is incorrect, incomplete, unavailable or produces an error. Add an Issue Ref.", "BodySmall")],
        [P("[  ] N/A", "BoldSmall"), P("Test cannot apply to this tester/data set. State the reason in the issue log or notes.", "BodySmall")],
    ], colWidths=[30 * mm, 149 * mm])
    legend.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.45, MID_GREY),
        ("BACKGROUND", (0, 0), (0, -1), PALE_GREY),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    story.append(legend)
    story.append(Spacer(1, 7 * mm))
    story.append(rich("<b>Important:</b> Use only authorised test accounts and non-sensitive test data. Do not upload confidential research material into a non-production environment.", "Tiny"))
    story.append(PageBreak())


def instructions(story):
    story.append(P("1. Test execution instructions", "Section"))
    numbered = [
        "Prepare an active Student test account, an inactive Student account, and test data covering current-semester enrolment, activities, deadlines, submissions, reviews, evaluators and correction states.",
        "For each test case, perform the stated action and compare the actual result with the expected result.",
        "Tick exactly one of PASS, FAIL or N/A. For FAIL, assign an issue reference such as ISS-01 and complete the issue log.",
        "Capture useful evidence: screenshot, exact error message, date/time, browser/device, activity/document name and steps to reproduce.",
        "After a destructive test such as removal or password change, restore the test account/data if later cases depend on it.",
        "The final acceptance decision should be made only after critical/high issues are resolved or formally accepted by the system owner.",
    ]
    data = []
    for i, text in enumerate(numbered, 1):
        data.append([P(str(i), "BoldSmall"), P(text, "Body")])
    t = Table(data, colWidths=[8 * mm, 171 * mm])
    t.setStyle(TableStyle([
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("BACKGROUND", (0, 0), (0, -1), PALE_BLUE),
        ("BOX", (0, 0), (-1, -1), 0.5, MID_GREY),
        ("INNERGRID", (0, 0), (-1, -1), 0.35, MID_GREY),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    story.append(t)
    story.append(Spacer(1, 6 * mm))
    story.append(P("Recommended test-data matrix", "Section"))
    matrix = [
        [P("Data set", "TableHead"), P("Minimum condition", "TableHead")],
        [P("Student accounts", "BoldSmall"), P("Active, inactive, active-current-semester, inactive-current-semester", "BodySmall")],
        [P("Submission dates", "BoldSmall"), P("Future, due today, due tomorrow, within 7 days, overdue", "BodySmall")],
        [P("Document states", "BoldSmall"), P("No Attempt, Submitted, Overdue, Locked, archived/prohibited", "BodySmall")],
        [P("Activity workflow", "BoldSmall"), P("Open, pending approvals, approved, rejected, evaluation, correction, failed, continue next semester", "BodySmall")],
        [P("Files", "BoldSmall"), P("Valid PDF, non-PDF, PDF at/below limit, PDF above 100 MB, JPG/PNG profile photo, invalid photo", "BodySmall")],
        [P("Academic records", "BoldSmall"), P("Material available/missing, final document, correction document, evaluation report, reviews, evaluator roles, journal records", "BodySmall")],
    ]
    m = Table(matrix, colWidths=[42 * mm, 137 * mm], repeatRows=1)
    m.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), NAVY),
        ("GRID", (0, 0), (-1, -1), 0.45, MID_GREY),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, PALE_GREY]),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    story.append(m)
    story.append(PageBreak())


def add_checklist_section(story, section_name, cases):
    story.append(P(section_name, "Section"))
    rows = [[
        P("ID", "TableHead"),
        P("Test function, action and expected result", "TableHead"),
        P("Result", "TableHead"),
        P("Issue Ref.", "TableHead"),
    ]]
    for case in cases:
        detail = (
            f"<b>{escape(case['title'])}</b><br/>"
            f"<font color='#5F6B76'><b>Action:</b> {escape(case['action'])}</font><br/>"
            f"<b>Expected:</b> {escape(case['expected'])}"
        )
        rows.append([
            P(case["id"], "BoldSmall"),
            rich(detail, "TableCell"),
            P("[  ] Pass\n[  ] Fail\n[  ] N/A", "TableCellCenter"),
            P("", "TableCellCenter"),
        ])
    table = Table(
        rows,
        colWidths=[18 * mm, 125 * mm, 23 * mm, 13 * mm],
        repeatRows=1,
        splitByRow=True,
        splitInRow=False,
    )
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), BLUE),
        ("GRID", (0, 0), (-1, -1), 0.45, MID_GREY),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, PALE_GREY]),
        ("ALIGN", (0, 1), (0, -1), "CENTER"),
        ("ALIGN", (2, 1), (-1, -1), "CENTER"),
        ("LEFTPADDING", (0, 0), (-1, -1), 4),
        ("RIGHTPADDING", (0, 0), (-1, -1), 4),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    story.append(table)
    story.append(Spacer(1, 5 * mm))


def issue_log(story):
    story.append(PageBreak())
    story.append(P("2. Issue / defect log", "Section"))
    story.append(P("Complete one block for every failed test. Attach screenshots or recordings separately and use the same Issue Ref.", "Body"))
    story.append(Spacer(1, 4 * mm))
    for idx in range(1, 7):
        top = Table([
            [P(f"ISS-{idx:02d}", "IssueTitle"), P("Related Test ID: ____________________", "BodySmall"),
             P("Severity: [  ] Critical  [  ] High  [  ] Medium  [  ] Low", "BodySmall")],
            [P("Issue title", "BoldSmall"), "", ""],
            [P("Steps to reproduce", "BoldSmall"), "", ""],
            [P("Actual result / error message", "BoldSmall"), "", ""],
            [P("Expected result", "BoldSmall"), "", ""],
            [P("Evidence / screenshot filename", "BoldSmall"), "", ""],
        ], colWidths=[42 * mm, 58 * mm, 79 * mm], rowHeights=[8 * mm, 8 * mm, 14 * mm, 14 * mm, 10 * mm, 8 * mm])
        top.setStyle(TableStyle([
            ("SPAN", (1, 1), (2, 1)),
            ("SPAN", (1, 2), (2, 2)),
            ("SPAN", (1, 3), (2, 3)),
            ("SPAN", (1, 4), (2, 4)),
            ("SPAN", (1, 5), (2, 5)),
            ("BACKGROUND", (0, 0), (-1, 0), PALE_BLUE),
            ("BACKGROUND", (0, 1), (0, -1), PALE_GREY),
            ("GRID", (0, 0), (-1, -1), 0.5, MID_GREY),
            ("VALIGN", (0, 0), (-1, -1), "TOP"),
            ("LEFTPADDING", (0, 0), (-1, -1), 5),
            ("RIGHTPADDING", (0, 0), (-1, -1), 5),
            ("TOPPADDING", (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
        ]))
        story.append(KeepTogether([top, Spacer(1, 5 * mm)]))
        if idx == 3:
            story.append(PageBreak())
            story.append(P("2. Issue / defect log (continued)", "Section"))


def feedback_and_signoff(story):
    story.append(PageBreak())
    story.append(P("3. Tester feedback and improvement suggestions", "Section"))
    prompts = [
        ("Overall feedback on the Student experience", 35 * mm),
        ("Functions that were confusing or difficult to use", 30 * mm),
        ("Suggestions for improvement", 35 * mm),
        ("Missing function(s) expected by the tester", 25 * mm),
        ("Additional comments", 25 * mm),
    ]
    for label, height in prompts:
        box = Table([[P(label, "BoldSmall")], [""]], colWidths=[179 * mm], rowHeights=[8 * mm, height])
        box.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, 0), PALE_BLUE),
            ("GRID", (0, 0), (-1, -1), 0.55, MID_GREY),
            ("LEFTPADDING", (0, 0), (-1, -1), 6),
            ("RIGHTPADDING", (0, 0), (-1, -1), 6),
            ("TOPPADDING", (0, 0), (-1, -1), 4),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
        ]))
        story.append(box)
        story.append(Spacer(1, 4 * mm))

    story.append(PageBreak())
    story.append(P("4. Test summary and sign-off", "Section"))
    summary = Table([
        [P("Total test cases", "BoldSmall"), P(str(TOTAL_CASES), "TableCellCenter"), P("Pass", "BoldSmall"), "", P("Fail", "BoldSmall"), "", P("N/A", "BoldSmall"), ""],
        [P("Open Critical", "BoldSmall"), "", P("Open High", "BoldSmall"), "", P("Open Medium", "BoldSmall"), "", P("Open Low", "BoldSmall"), ""],
    ], colWidths=[30 * mm, 13 * mm, 23 * mm, 13 * mm, 18 * mm, 13 * mm, 18 * mm, 13 * mm])
    summary.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.55, MID_GREY),
        ("BACKGROUND", (0, 0), (0, -1), PALE_BLUE),
        ("BACKGROUND", (2, 0), (2, -1), PALE_BLUE),
        ("BACKGROUND", (4, 0), (4, -1), PALE_BLUE),
        ("BACKGROUND", (6, 0), (6, -1), PALE_BLUE),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("ALIGN", (1, 0), (-1, -1), "CENTER"),
        ("LEFTPADDING", (0, 0), (-1, -1), 5),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 7),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
    ]))
    story.append(summary)
    story.append(Spacer(1, 8 * mm))

    decision = Table([
        [P("Overall UAT decision", "BoldSmall"), P("[  ] Accepted    [  ] Accepted with conditions    [  ] Re-test required    [  ] Rejected", "Body")],
        [P("Conditions / outstanding items", "BoldSmall"), ""],
    ], colWidths=[45 * mm, 134 * mm], rowHeights=[12 * mm, 35 * mm])
    decision.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.55, MID_GREY),
        ("BACKGROUND", (0, 0), (0, -1), PALE_BLUE),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
    ]))
    story.append(decision)
    story.append(Spacer(1, 12 * mm))

    sign = Table([
        [P("Student Tester", "TableHead"), "", P("UAT Coordinator / Witness", "TableHead"), ""],
        [P("Name", "BodySmall"), "", P("Name", "BodySmall"), ""],
        [P("Signature", "BodySmall"), "", P("Signature", "BodySmall"), ""],
        [P("Date", "BodySmall"), "", P("Date", "BodySmall"), ""],
    ], colWidths=[20 * mm, 69.5 * mm, 28 * mm, 61.5 * mm], rowHeights=[9 * mm, 10 * mm, 20 * mm, 10 * mm])
    sign.setStyle(TableStyle([
        ("SPAN", (0, 0), (1, 0)),
        ("SPAN", (2, 0), (3, 0)),
        ("BACKGROUND", (0, 0), (1, 0), NAVY),
        ("BACKGROUND", (2, 0), (3, 0), NAVY),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
        ("GRID", (0, 0), (-1, -1), 0.55, MID_GREY),
        ("BACKGROUND", (0, 1), (0, -1), PALE_GREY),
        ("BACKGROUND", (2, 1), (2, -1), PALE_GREY),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    story.append(sign)
    story.append(Spacer(1, 8 * mm))
    story.append(P("Document control: Student-role checklist derived from the application routes, controllers and rendered student interfaces available at the time of preparation.", "Tiny"))


def build():
    OUT.parent.mkdir(parents=True, exist_ok=True)
    doc = UATDocTemplate(str(OUT))
    story = []
    cover_page(story)
    instructions(story)
    for section_name, cases in SECTIONS:
        add_checklist_section(story, section_name, cases)
    issue_log(story)
    feedback_and_signoff(story)
    doc.build(story)
    print(OUT)


if __name__ == "__main__":
    build()
