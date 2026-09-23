from pathlib import Path
from xml.sax.saxutils import escape

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    Image,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "output" / "pdf" / "e-Pasca_Functions_by_Role.pdf"
LOGO = ROOT / "public" / "assets" / "images" / "logo-utem.PNG"

PAGE_W, PAGE_H = A4
NAVY = colors.HexColor("#17365D")
BLUE = colors.HexColor("#2C5F8A")
LIGHT_BLUE = colors.HexColor("#EAF2F8")
LIGHT_GREY = colors.HexColor("#F5F6F7")
LINE = colors.HexColor("#C9D2DB")
TEXT = colors.HexColor("#20252A")
MUTED = colors.HexColor("#5F6872")


def register_fonts():
    font_files = {
        "TimesNewRoman": "C:/Windows/Fonts/times.ttf",
        "TimesNewRoman-Bold": "C:/Windows/Fonts/timesbd.ttf",
        "TimesNewRoman-Italic": "C:/Windows/Fonts/timesi.ttf",
    }
    for name, path in font_files.items():
        if Path(path).exists():
            pdfmetrics.registerFont(TTFont(name, path))
    regular = "TimesNewRoman" if "TimesNewRoman" in pdfmetrics.getRegisteredFontNames() else "Times-Roman"
    bold = "TimesNewRoman-Bold" if "TimesNewRoman-Bold" in pdfmetrics.getRegisteredFontNames() else "Times-Bold"
    italic = "TimesNewRoman-Italic" if "TimesNewRoman-Italic" in pdfmetrics.getRegisteredFontNames() else "Times-Italic"
    return regular, bold, italic


REGULAR, BOLD, ITALIC = register_fonts()

STYLES = {
    "title": ParagraphStyle(
        "title", fontName=BOLD, fontSize=22, leading=27, textColor=NAVY,
        alignment=TA_CENTER, spaceAfter=5,
    ),
    "subtitle": ParagraphStyle(
        "subtitle", fontName=REGULAR, fontSize=12, leading=16, textColor=MUTED,
        alignment=TA_CENTER,
    ),
    "role": ParagraphStyle(
        "role", fontName=BOLD, fontSize=16, leading=20, textColor=colors.white,
        backColor=NAVY, borderPadding=(7, 9, 7, 9), spaceAfter=7, keepWithNext=True,
    ),
    "subhead": ParagraphStyle(
        "subhead", fontName=BOLD, fontSize=13, leading=16, textColor=NAVY,
        spaceBefore=5, spaceAfter=5, keepWithNext=True,
    ),
    "body": ParagraphStyle(
        "body", fontName=REGULAR, fontSize=12, leading=15, textColor=TEXT,
    ),
    "body_bold": ParagraphStyle(
        "body_bold", fontName=BOLD, fontSize=12, leading=15, textColor=TEXT,
    ),
    "body_italic": ParagraphStyle(
        "body_italic", fontName=ITALIC, fontSize=12, leading=15, textColor=MUTED,
    ),
    "table_head": ParagraphStyle(
        "table_head", fontName=BOLD, fontSize=12, leading=14, textColor=colors.white,
        alignment=TA_CENTER,
    ),
    "table_body": ParagraphStyle(
        "table_body", fontName=REGULAR, fontSize=12, leading=14.5, textColor=TEXT,
    ),
    "table_bold": ParagraphStyle(
        "table_bold", fontName=BOLD, fontSize=12, leading=14.5, textColor=TEXT,
    ),
    "footer": ParagraphStyle(
        "footer", fontName=REGULAR, fontSize=9, leading=11, textColor=MUTED,
    ),
}


def p(text, style="body"):
    return Paragraph(escape(str(text)).replace("\n", "<br/>"), STYLES[style])


def rich(text, style="body"):
    return Paragraph(text, STYLES[style])


COMMON = [
    ("Login to e-Pasca", "Login page - enter the registered email and password."),
    ("Reset forgotten password", "Login page - select Forgot Password and follow the email link."),
    ("View dashboard", "Sidebar - select Dashboard."),
    ("Switch working role", "Staff sidebar - use My Role when more than one role is available."),
    ("Update personal profile", "Top-right account menu - My Profile - Personal Details."),
    ("Change profile photo", "My Profile - Personal Details - Change Photo or Reset Photo."),
    ("Change password", "My Profile - Change Password."),
    ("Logout", "Top-right account menu - Logout."),
]

STUDENT = [
    ("View student dashboard", "Dashboard - view personal information and current alerts."),
    ("View research title", "Dashboard - Research Title section."),
    ("Update research title", "Dashboard - Research Title - select Edit, enter the title and save."),
    ("View supervisor and co-supervisor", "Dashboard - Supervisor and Co-Supervisor sections."),
    ("Contact supervisor by email", "Dashboard - select the supervisor or co-supervisor email link."),
    ("View upcoming reminders", "Dashboard - Reminders - Upcoming tab."),
    ("View overdue reminders", "Dashboard - Reminders - Overdue tab."),
    ("Open a document from a reminder", "Dashboard - select the required document in the reminder list."),
    ("View assigned evaluators", "Dashboard - Nomination Details - view Examiner, Panel or Chairman information."),
    ("View programme activities", "Sidebar - Programme Overview."),
    ("View activity and document status", "Programme Overview - check each activity card and document status."),
    ("Download activity material", "Programme Overview - Flowchart / Material - select Download."),
    ("View final document", "Programme Overview - Final Document - select View Final Document."),
    ("View correction document", "Programme Overview - select View Final Document (Correction), when available."),
    ("View evaluation report", "Programme Overview - Evaluation Report - select the report file."),
    ("View submission review comments", "Programme Overview - Reviews - select View Review."),
    ("Submit a document", "Programme Overview - select Submit Document - add one PDF - Save Changes."),
    ("View submitted document", "Programme Overview - select View Submission - select the PDF file name."),
    ("Update a submitted document", "Document Submission page - select Update Submission - replace the PDF - Save Changes."),
    ("Remove a submitted document", "Document Submission page - select Remove Submission - confirm deletion."),
    ("Confirm final submission", "Programme Overview - after all required documents are submitted, select Confirm Submission - sign - Confirm & Sign."),
    ("Confirm corrected submission", "Programme Overview - for a correction activity, select Confirm Correction - sign and confirm."),
    ("View journal publications", "Sidebar - Journal Publication."),
    ("Add journal publication", "Journal Publication - select Add Journal Publication - complete the details - save."),
    ("Update journal publication", "Journal Publication - select Edit for the required journal - update and save."),
    ("Delete journal publication", "Journal Publication - select Delete for the required journal - confirm deletion."),
]

ADMIN_SHARED = [
    ("View administrator dashboard", "My Role - Administrator - Dashboard."),
    ("View pending approvals and progress", "Administrator Dashboard - review Pending Approvals, Semester Overview and Student Progress."),
    ("View students needing follow-up", "Administrator Dashboard - Students Needing Follow-up - select Review."),
    ("Manage student records", "Supervision - Student - Student Management - add, update, inactivate or delete a student."),
    ("Import student records", "Student Management - Import Student - upload the completed Excel template."),
    ("Export student records", "Student Management - Export Data."),
    ("Update multiple student statuses", "Student Management - select students - Change Status."),
    ("Manage staff records", "Supervision - Staff Management - add, update, inactivate or delete staff."),
    ("Import staff records", "Staff Management - Import Staff - upload the completed Excel template."),
    ("Export staff records", "Staff Management - Export Data."),
    ("Arrange student supervision", "Supervision - Supervision Arrangement - assign or update main supervisor and co-supervisor."),
    ("Update student research title", "Supervision Arrangement - select the student - update Title of Research."),
    ("Export supervision records", "Supervision Arrangement - Export Data."),
    ("View semester enrolment", "Supervision - Student - Semester Enrollment - select a semester."),
    ("Enrol students in a semester", "Semester Enrollment - open a semester - Enrol Student."),
    ("Import or export semester enrolment", "Semester student list - use Import Student or Export Data."),
    ("Update or remove semester enrolment", "Semester student list - update enrolment status or delete selected students."),
    ("View final submissions", "Submission - Final Overview."),
    ("Update, delete or export final submissions", "Submission - Final Overview - use the row action or Export Data."),
    ("Manage submission setup", "Submission - Submission Management - add or update submission dates and settings."),
    ("Archive or restore submissions", "Submission Management - use Archive or Unarchive for the selected submission."),
    ("Bulk-manage submissions", "Submission Management - select records - update, archive, download or export."),
    ("Approve or reject student submissions", "Submission - Submission Approval - open the student submission - approve or reject."),
    ("Add or update submission review", "Submission Approval - open Review - add, update or delete comments."),
    ("Check and approve submission eligibility", "Submission - Submission Eligibility - approve or reject one or multiple students."),
    ("View nomination final records", "Nomination - Final Overview - select the required activity."),
    ("Update, delete, re-nominate or export nomination records", "Nomination - Final Overview - use the available row action or Export Data."),
    ("View evaluation final records", "Evaluation - Final Overview - select the required activity."),
    ("Update, delete or export evaluation records", "Evaluation - Final Overview - use the available row action or Export Data."),
    ("View correction final records", "Evaluation - Final Overview - Correction."),
    ("Update, delete or export correction records", "Correction Final Overview - use the available row action or Export Data."),
    ("Approve or reject corrections", "Evaluation - Correction Approval - open the student correction - approve or reject."),
    ("Manage programme procedures", "SOP - Procedure Setting - add, update or delete a programme activity procedure."),
    ("View procedure material", "Procedure Setting - select the material link for the required activity."),
    ("Manage activities and documents", "SOP - Activity Setting - add, update or delete activities and their documents."),
    ("Manage activity forms", "SOP - Form Setting - add or delete a form for an activity."),
    ("Build and preview forms", "Form Setting - open Form Editor - add, update, arrange or delete fields - preview the form."),
    ("Manage faculty", "System Setting - Faculty Setting - add, update, inactivate/delete or set the default faculty."),
    ("Manage department", "System Setting - Department Setting - add, update or inactivate/delete a department."),
    ("Manage programme", "System Setting - Programme Setting - add, update or inactivate/delete a programme."),
    ("Manage semester", "System Setting - Semester Setting - add, update or inactivate/delete a semester."),
    ("Change the current semester", "Semester Setting - Set Current Semester - review the impact preview - confirm the change."),
]

ADMIN_ROLE_SPECIFIC = [
    ("Committee - view audit log", "My Role - Administrator - System Setting - Audit Log."),
    ("Committee - approve nominations", "Nomination - Nomination Approval - open the activity and approve or reject the nomination."),
    ("Deputy Dean - approve nominations", "Nomination - Nomination Approval, when the activity requires the Deputy Dean signature."),
    ("Dean - approve nominations", "Nomination - Nomination Approval, when the activity requires the Dean signature."),
    ("Committee - approve evaluations", "Evaluation - Approval - select the activity - review and approve or reject."),
    ("Deputy Dean - approve evaluations", "Evaluation - Approval, when the activity requires the Deputy Dean signature."),
    ("Dean - approve evaluations", "Evaluation - Approval, when the activity requires the Dean signature."),
    ("Finalize an evaluation", "Evaluation Approval - after all required approvals are completed, select Finalize Evaluation."),
]

SUPERVISOR = [
    ("View supervisor dashboard", "My Role - Supervisor - Dashboard."),
    ("View tasks requiring action", "Supervisor Dashboard - Tasks Requiring Your Action - select Review."),
    ("View supervised students", "Supervisor Dashboard - My Students Overview, or Sidebar - My Student."),
    ("Filter and view student details", "My Student - use the available filters and open the required student."),
    ("Export supervised-student list", "My Student - Export Data."),
    ("View student document submissions", "Submission - Submission Management."),
    ("Download a student submission", "Submission Management - open the row menu - Download."),
    ("Update or archive a student submission", "Submission Management - open the row menu - Setting, Archive or Unarchive."),
    ("Approve or reject final submission", "Submission - Submission Approval - open the student record - approve or reject."),
    ("Add or update review comments", "Submission Approval - open Review - add, update or delete a comment."),
    ("Nominate evaluators", "Sidebar - Nomination - select the activity/student - complete and sign the nomination form."),
    ("Approve student evaluation", "Evaluation - select the activity shown under the Supervisor role - review and approve or reject."),
    ("Approve or reject student correction", "Evaluation - Correction Approval - open the correction - approve or reject."),
]

EXAMINER_PANEL = [
    ("View assigned evaluation activities", "My Role - Examiner / Panel - Evaluation - select an activity."),
    ("View students assigned for evaluation", "Evaluation activity page - review the student list and status."),
    ("Open evaluation form", "Evaluation activity page - select the student or evaluation action."),
    ("Complete evaluation", "Evaluation form - enter the required marks, decisions and comments."),
    ("Sign and submit evaluation", "Evaluation form - provide the electronic signature - submit the evaluation."),
    ("View previous evaluation details", "Evaluation activity page - open an existing evaluation where available."),
    ("Approve or reject correction", "Examiner / Panel - Evaluation - Correction Approval - open the correction - approve or reject."),
]

CHAIRMAN = [
    ("View chairman evaluation activities", "My Role - Chairman - Evaluation - select an activity."),
    ("View students ready for chairman action", "Chairman evaluation page - review the student list and current status."),
    ("Review completed evaluator forms", "Open the student evaluation and review the submitted Examiner or Panel information."),
    ("Complete chairman evaluation", "Chairman evaluation form - enter the required decision and comments."),
    ("Sign and submit chairman evaluation", "Chairman evaluation form - provide the electronic signature - submit the evaluation."),
]

LECTURER = [
    ("Use standard staff account functions", "Login, Dashboard, My Profile, Change Password and Logout are available to every active staff account."),
    ("Use an assigned working role", "If assigned as Supervisor, Examiner/Panel or Chairman, select that role from My Role and test the functions listed in its section."),
    ("View only authorised work", "Confirm that students and activities not assigned to the lecturer are not shown in the working-role pages."),
]


class RoleDoc(BaseDocTemplate):
    def __init__(self, path):
        super().__init__(
            path,
            pagesize=A4,
            leftMargin=15 * mm,
            rightMargin=15 * mm,
            topMargin=22 * mm,
            bottomMargin=17 * mm,
            title="e-Pasca Functions by Role",
            author="Universiti Teknikal Malaysia Melaka",
            subject="High-level system functions grouped by user role",
        )
        frame = Frame(self.leftMargin, self.bottomMargin, self.width, self.height, id="content")
        self.addPageTemplates([PageTemplate(id="standard", frames=[frame], onPage=draw_header_footer)])


def draw_header_footer(canvas, doc):
    canvas.saveState()
    page = canvas.getPageNumber()
    if page > 1:
        if LOGO.exists():
            canvas.drawImage(
                str(LOGO), 15 * mm, PAGE_H - 13 * mm,
                width=23 * mm, height=10 * mm, preserveAspectRatio=True, anchor="sw", mask="auto",
            )
        canvas.setFont(BOLD, 10)
        canvas.setFillColor(NAVY)
        canvas.drawRightString(PAGE_W - 15 * mm, PAGE_H - 9 * mm, "e-Pasca Functions by Role")
        canvas.setStrokeColor(LINE)
        canvas.line(15 * mm, PAGE_H - 15 * mm, PAGE_W - 15 * mm, PAGE_H - 15 * mm)
    canvas.setStrokeColor(LINE)
    canvas.line(15 * mm, 12 * mm, PAGE_W - 15 * mm, 12 * mm)
    canvas.setFont(REGULAR, 9)
    canvas.setFillColor(MUTED)
    canvas.drawString(15 * mm, 8 * mm, "Universiti Teknikal Malaysia Melaka | Internal Use")
    canvas.drawRightString(PAGE_W - 15 * mm, 8 * mm, f"Page {page}")
    canvas.restoreState()


def function_table(items, start_number=1):
    rows = [[p("No.", "table_head"), p("Function", "table_head"), p("Where to go / How to test", "table_head"), p("Tick", "table_head")]]
    for number, (function, where) in enumerate(items, start_number):
        rows.append([
            p(str(number), "table_body"),
            p(function, "table_bold"),
            p(where, "table_body"),
            p("[   ]", "table_body"),
        ])
    table = Table(
        rows,
        colWidths=[12 * mm, 51 * mm, 105 * mm, 12 * mm],
        repeatRows=1,
        splitByRow=True,
        splitInRow=False,
    )
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), BLUE),
        ("GRID", (0, 0), (-1, -1), 0.5, LINE),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, LIGHT_GREY]),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("ALIGN", (0, 1), (0, -1), "CENTER"),
        ("ALIGN", (3, 1), (3, -1), "CENTER"),
        ("LEFTPADDING", (0, 0), (-1, -1), 5),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
    ]))
    return table


def add_role(story, title, intro, items, force_page=True):
    if force_page:
        story.append(PageBreak())
    story.append(p(title, "role"))
    if intro:
        note = Table([[p(intro, "body")]], colWidths=[180 * mm])
        note.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, -1), LIGHT_BLUE),
            ("BOX", (0, 0), (-1, -1), 0.6, BLUE),
            ("LEFTPADDING", (0, 0), (-1, -1), 7),
            ("RIGHTPADDING", (0, 0), (-1, -1), 7),
            ("TOPPADDING", (0, 0), (-1, -1), 6),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
        ]))
        story.append(note)
        story.append(Spacer(1, 5 * mm))
    story.append(function_table(items))


def cover(story):
    story.append(Spacer(1, 12 * mm))
    if LOGO.exists():
        logo = Image(str(LOGO), width=55 * mm, height=24 * mm)
        logo.hAlign = "CENTER"
        story.append(logo)
    story.append(Spacer(1, 12 * mm))
    story.append(p("e-Pasca", "title"))
    story.append(p("FUNCTIONS BY ROLE", "title"))
    story.append(p("Simple Function Review Guide", "subtitle"))
    story.append(Spacer(1, 14 * mm))

    info = Table([
        [p("Reviewer Name", "body_bold"), ""],
        [p("Role(s) Tested", "body_bold"), ""],
        [p("Review Date", "body_bold"), ""],
    ], colWidths=[45 * mm, 135 * mm], rowHeights=[13 * mm] * 3)
    info.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.6, LINE),
        ("BACKGROUND", (0, 0), (0, -1), LIGHT_BLUE),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("LEFTPADDING", (0, 0), (-1, -1), 7),
        ("RIGHTPADDING", (0, 0), (-1, -1), 7),
    ]))
    story.append(info)
    story.append(Spacer(1, 12 * mm))

    guide = Table([[rich(
        "<b>How to use:</b> Select your role, follow the page path, test the function and tick the box when completed. "
        "If a function is not shown in your account, it may require an assignment or system configuration.",
        "body")]], colWidths=[180 * mm])
    guide.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), LIGHT_BLUE),
        ("BOX", (0, 0), (-1, -1), 0.8, BLUE),
        ("LEFTPADDING", (0, 0), (-1, -1), 9),
        ("RIGHTPADDING", (0, 0), (-1, -1), 9),
        ("TOPPADDING", (0, 0), (-1, -1), 8),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 8),
    ]))
    story.append(guide)
    story.append(Spacer(1, 12 * mm))
    story.append(p("Roles covered", "subhead"))
    roles = [
        "Student",
        "Committee",
        "Deputy Dean",
        "Dean",
        "Supervisor / Co-Supervisor",
        "Examiner / Panel",
        "Chairman",
        "Lecturer / Other Staff",
    ]
    role_rows = []
    for i in range(0, len(roles), 2):
        role_rows.append([p("[   ] " + roles[i], "body"), p("[   ] " + roles[i + 1], "body")])
    rt = Table(role_rows, colWidths=[90 * mm, 90 * mm])
    rt.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.45, LINE),
        ("ROWBACKGROUNDS", (0, 0), (-1, -1), [colors.white, LIGHT_GREY]),
        ("LEFTPADDING", (0, 0), (-1, -1), 8),
        ("RIGHTPADDING", (0, 0), (-1, -1), 8),
        ("TOPPADDING", (0, 0), (-1, -1), 7),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
    ]))
    story.append(rt)


def build():
    OUT.parent.mkdir(parents=True, exist_ok=True)
    doc = RoleDoc(str(OUT))
    story = []
    cover(story)
    add_role(
        story,
        "Functions for All Users",
        "These functions apply to students and staff. Staff members should also test each working role assigned to their account.",
        COMMON,
    )
    add_role(
        story,
        "Student",
        "Use the Student sidebar. Functions may appear only when the student is active in the current semester and the related activity is open.",
        STUDENT,
    )
    add_role(
        story,
        "Committee / Deputy Dean / Dean - Shared Administrator Functions",
        "Select My Role - Administrator. These three staff roles share the administration pages below.",
        ADMIN_SHARED,
    )
    add_role(
        story,
        "Committee / Deputy Dean / Dean - Role-Specific Approvals",
        "Approval pages appear only when the activity form requires that role. The Committee account also has access to the Audit Log.",
        ADMIN_ROLE_SPECIFIC,
    )
    add_role(
        story,
        "Supervisor / Co-Supervisor",
        "Select My Role - Supervisor. This role appears when the staff member is assigned to at least one student.",
        SUPERVISOR,
    )
    add_role(
        story,
        "Examiner / Panel",
        "Select My Role - Examiner / Panel. Only assigned and approved evaluation activities are shown.",
        EXAMINER_PANEL,
    )
    add_role(
        story,
        "Chairman",
        "Select My Role - Chairman. Only activities where the staff member is assigned as Chairman are shown.",
        CHAIRMAN,
    )
    add_role(
        story,
        "Lecturer / Other Staff",
        "A lecturer may have one or more additional working roles. Test the sections that appear in My Role.",
        LECTURER,
    )

    story.append(Spacer(1, 8 * mm))
    story.append(p("General comments", "subhead"))
    comments = Table([[""]], colWidths=[180 * mm], rowHeights=[55 * mm])
    comments.setStyle(TableStyle([("BOX", (0, 0), (-1, -1), 0.6, LINE)]))
    story.append(comments)
    doc.build(story)
    print(OUT)


if __name__ == "__main__":
    build()
