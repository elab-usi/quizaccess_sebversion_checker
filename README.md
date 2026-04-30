# quizaccess_sebversion_checker

A Moodle access rule plugin that monitors and enforces specific Safe Exam Browser (SEB) versions for students during quiz attempts. It provides administrative reporting and automated user session tracking.

# Installation
MOODLE_DIR/mod/quiz/accessrule/sebversion_checker

# Features
* Version Enforcement: Prevents students from starting a quiz if their SEB version doesn't match the allowed versions defined in the settings.

* Automated Session Sync: A daily scheduled task (Cron) that identifies users enrolled in upcoming exam sessions.

* Administrative Report: A dedicated dashboard for administrators to monitor:
  * Last detected SEB version per user.
  * Current exam session status.
  * Timestamp of the last access.
  
* Data Export: Built-in functionality to export SEB usage data to CSV