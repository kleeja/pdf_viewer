<?php
//
// pdf_viewer, its guide on the help page of the control panel
// Arabic, see help_en.php for how the words are named
//
// A word missing here is shown in English.
// Names in <strong> are written as the control panel and the viewer show them in Arabic,
// the words of Kleeja are in lang/ar/, the words of the viewer in pdf_viewer_translations() of init.php.
// A <code> that begins or ends with a symbol takes dir="ltr", or the symbol shows on the wrong side.
//

return [
    'PDF_VIEWER_HELP_TITLE' => 'عارض ملفات PDF',
    'PDF_VIEWER_HELP_INTRO' =>
        'يتيح للزوار قراءة ملف PDF في صفحة تحميله مباشرة، قبل أن يقرروا تنزيله. العارض مبني على PDF.js، قارئ ملفات PDF مفتوح المصدر الذي تطوّره Mozilla لمتصفح Firefox، ولا يحتاج إلى أي إعداد.',

    //
    // what the plugin does
    //
    'PDF_VIEWER_HELP_FEATURE_1' =>
        'يعرض المستند تحت معلومات الملف في صفحة تحميل كل ملف PDF، في ستايل Bootstrap والستايل الافتراضي Default والستايلات المبنية عليهما.',
    'PDF_VIEWER_HELP_FEATURE_2' =>
        'شريط أدوات للتنقل بين الصفحات، والانتقال إلى صفحة برقمها، والتكبير والتصغير، أو اختيار <strong>تكبير تلقائي</strong> أو <strong>الحجم الفعلي</strong> أو <strong>ملاءمة الصفحة</strong> أو <strong>ملاءمة العرض</strong> أو نسبة ثابتة من 50% إلى 200%.',
    'PDF_VIEWER_HELP_FEATURE_3' =>
        'يعرض <strong>ملء الشاشة</strong> العارض وحده. أما <strong>وضع العرض التقديمي</strong> فيعرض صفحة واحدة في كل مرة على الشاشة كلها: تنتقل بين الصفحات بمفاتيح الأسهم أو Space أو Home أو End أو بنقرة أو بعجلة الفأرة، وتنهيه بمفتاح Esc.',
    'PDF_VIEWER_HELP_FEATURE_4' =>
        'يستطيع الزوار تحديد نص المستند ونسخه. وتُفتح روابط المستند في تبويب جديد، ولا تعمل أبدًا النصوص البرمجية التي قد يحتويها ملف PDF.',
    'PDF_VIEWER_HELP_FEATURE_5' =>
        'تُفتح الملفات الكبيرة بسرعة: يقرأ العارض الصفحات التي يعرضها أولًا، ما دام خادم الويب يدعم طلبات الأجزاء (Range)، كما يفعل Apache وNginx.',
    'PDF_VIEWER_HELP_FEATURE_6' =>
        'يتبع لغة الصفحة واتجاهها ووضعها الفاتح أو الداكن. ويبقى زر التحميل في الصفحة كما هو.',

    //
    // how to use it
    //
    'PDF_VIEWER_HELP_STEP_TITLE' => 'اعرض أول ملف PDF',
    'PDF_VIEWER_HELP_STEP_1' =>
        'تأكد من أن مجموعاتك تستطيع رفع ملفات PDF: افتح <strong>الأعضاء والمجموعات</strong>، واختر <strong>إعدادات الإمتدادات</strong> لكل مجموعة، وأضف <code>pdf</code> إن لم يكن موجودًا.',
    'PDF_VIEWER_HELP_STEP_2' =>
        'افتح <strong>إعدادات</strong> ثم <strong>اعدادات التحميل</strong>، وتأكد من أن <code>pdf</code> ليس ضمن <strong>الامتدادات التي لاتمر على صفحة الانتظار</strong>، فهذه الملفات تتخطى صفحة التحميل، ومعها العارض.',
    'PDF_VIEWER_HELP_STEP_3' => 'ارفع ملف PDF، ثم افتح صفحة تحميله. يظهر العارض تحت معلومات الملف.',

    //
    // common questions
    //
    'PDF_VIEWER_HELP_FAQ_Q_1' => 'لا يظهر العارض في صفحة تحميل ملف PDF، لماذا؟',
    'PDF_VIEWER_HELP_FAQ_A_1' =>
        'لا يظهر العارض إلا للملفات التي امتدادها <code>pdf</code>، وفي صفحة تحميلها فقط. تأكد من أن <code>pdf</code> ليس ضمن <strong>الامتدادات التي لاتمر على صفحة الانتظار</strong>، لأن هذه الملفات لا تمر بتلك الصفحة أبدًا. وإذا كان لستايلك قالب تحميل خاص به، فيجب أن يُبقي على ما تضيفه الإضافة إليه، كما يفعل ستايل Bootstrap والستايل الافتراضي.',
    'PDF_VIEWER_HELP_FAQ_Q_2' => 'يقول العارض إن معاينة الملف تتعذّر، لماذا؟',
    'PDF_VIEWER_HELP_FAQ_A_2' =>
        'إما أن المتصفح لم يستطع تحميل PDF.js من jsDelivr (جدار حماية، أو مانع محتوى، أو شبكة بلا اتصال بالإنترنت)، وإما أنه لم يستطع قراءة الملف (الملف تالف، أو خادم الويب يمنع الوصول المباشر إلى مجلد الرفع). ولا تستطيع المتصفحات القديمة جدًا تشغيل العارض أيضًا. تبيّن وحدة التحكم (Console) في أدوات المطوّر بالمتصفح السبب، ويبقى بإمكان الزوار تنزيل الملف.',
    'PDF_VIEWER_HELP_FAQ_Q_3' => 'ماذا عن الملفات المحمية بكلمة مرور؟',
    'PDF_VIEWER_HELP_FAQ_A_3' =>
        'لا يطلب العارض كلمات المرور، بل يخبر الزوار بأن الملف محمي، فيفتحونه في قارئ PDF الخاص بهم بعد تنزيله.',
    'PDF_VIEWER_HELP_FAQ_Q_4' => 'هل تُحسب قراءة الملف في العارض تحميلًا له؟',
    'PDF_VIEWER_HELP_FAQ_A_4' =>
        'لا. يقرأ العارض الملف من مجلد الرفع مباشرة، فلا يُحسب تحميلًا، ولا ينتظر <strong>عدد الثواني قبل بدء التحميل</strong> الخاص بالمجموعة. أما زر التحميل في الصفحة فيبقى يفعل الأمرين.',
    'PDF_VIEWER_HELP_FAQ_Q_5' => 'تحفظ إحدى الإضافات ملفاتي على خادم آخر، فهل يعمل العارض معها؟',
    'PDF_VIEWER_HELP_FAQ_A_5' =>
        'نعم، إذا كان ذلك الخادم يسمح لموقعك بقراءة الملفات: يجب أن يرسل الترويسة <code>Access-Control-Allow-Origin</code> (CORS) مع عنوان موقعك، وأن يدعم طلبات الأجزاء للملفات الكبيرة.',

    //
    // beside the guide
    //
    'PDF_VIEWER_HELP_TIP_1' =>
        'يفيد العارض أكثر ما يفيد في المواقع التي تشارك المستندات أو الكتب أو النماذج: يتأكد الزوار من أن الملف هو ما يريدونه قبل تنزيله.',
    'PDF_VIEWER_HELP_TIP_2' => 'لا يُحمَّل العارض إلا في صفحات تحميل ملفات PDF، فتبقى بقية صفحات موقعك خفيفة كما كانت.',

    'PDF_VIEWER_HELP_WARNING_1' =>
        'يقرأ العارض كل ملف من عنوانه المباشر في مجلد الرفع. وأي قاعدة في خادم الويب تمنع الوصول المباشر إلى هذا المجلد، مثل <code dir="ltr">deny all;</code> أو <code dir="ltr">internal;</code> في Nginx، تمنع العارض أيضًا.',
    'PDF_VIEWER_HELP_WARNING_2' =>
        'يُحمَّل PDF.js من شبكة jsDelivr. والزوار الذين تحجب شبكتهم jsDelivr يرون رسالة بدلًا من المستند.',
];
