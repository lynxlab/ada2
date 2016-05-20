# ADA scorm Module

Implementation and installation notes:

* Make sure the ``SCOObjects`` directory is readable and writable by the webserver.

* The only implemented and default mode is _credit_, that's to say that the learner will be credited for performance in the SCO. Looks like is up to ADA to determine wheter the SCO shall be _credit_ or _no-credit_ and this choice shall be object of future implementations, if and where needed.

* **cmi.core.lesson_mode** is not supported for time being. This, together, with the _credit_ mode will affect the calculation of **cmi.core.lesson_status**. Pls referer to [http://www.vsscorm.net/2009/07/24/step-22-progress-and-completion-cmi-core-lesson_status/](http://www.vsscorm.net/2009/07/24/step-22-progress-and-completion-cmi-core-lesson_status) for a detailed explanation.

* **adlcp:masteryscore** is supported vor v1.2 only, objectives logic of SCORM 2004 is not implemented at all.

* **cmi.core.lesson_status** is computed according to :[http://www.vsscorm.net/2009/07/29/step-23-more-about-cmi-core-lesson_status/](http://www.vsscorm.net/2009/07/29/step-23-more-about-cmi-core-lesson_status/)

## SCORM v1.2 and 2004 2nd, 3rd and 4th Edition Supported elements

|   Element name v1.2    | Element name 2004 | Read Only | Write Only | Read/Write |
|------------------------|------------------ |:---------:|:----------:|:----------:|
|cmi.core._children      |         -           |:white_check_mark:|:x:|:x:|
|cmi.core.student_id     |         -           |:white_check_mark:|:x:|:x:|
|cmi.core.student_name   |         -           |:white_check_mark:|:x:|:x:|
|cmi.core.lesson_location|cmi.location         |:x:|:x:|:white_check_mark:|
|cmi.core.credit         |cmi.credit           |:white_check_mark:|:x:|:x:|
|cmi.core.lesson_status  |cmi.completion_status|:x:|:x:|:white_check_mark:|
|cmi.core.entry          |cmi.entry            |:white_check_mark:|:x:|:x:|
|cmi.core.exit           |cmi.exit             |:x:|:white_check_mark:|:x:|
|cmi.core.score._children|cmi.score._children  |:white_check_mark:|:x:|:x:|
|cmi.core.score.raw      |cmi.score.raw        |:x:|:x:|:white_check_mark:|
|cmi.core.score.max      |cmi.score.max        |:x:|:x:|:white_check_mark:|
|cmi.core.score.min      |cmi.score.mix        |:x:|:x:|:white_check_mark:|
|cmi.core.total_time     |cmi.total_time       |:white_check_mark:|:x:|:x:|
|cmi.core.session_time   |cmi.session_time     |:x:|:white_check_mark:|:x:|
|cmi.suspend_data        |cmi.suspend_data     |:x:|:x:|:white_check_mark:|
|cmi.launch_data         |cmi.launch_data      |:white_check_mark:|:x:|:x:|
