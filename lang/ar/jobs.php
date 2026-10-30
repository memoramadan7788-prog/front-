<?php

return [
    'visibility' => [
        'public' => 'عامة',
        'private' => 'خاصة',
    ],

    'application_mode' => [
        'open' => 'تقديم مفتوح',
        'all_employees' => 'الموظفون فقط',
        'nominated_only' => 'بالترشيح فقط',
    ],

    'details' => [
        'role' => 'وظيفة شاغرة',
        'salary_title' => 'نطاق الراتب',
        'salary_not_listed' => 'غير محدد',
        'save_job' => 'حفظ الوظيفة',
        'saved_job' => 'محفوظة على هذا المتصفح',
        'save_unavailable' => 'الحفظ في المتصفح غير متاح. تحقق من تفعيل التخزين المحلي.',
        'consent_notice' => 'بالمتابعة، توافق على أن يراجع فريق التوظيف مواد طلبك المقدمة لهذه الوظيفة.',
        'journey_title' => 'مراحل التقديم',
        'journey_hint' => 'مراحل معتادة وليست حالة طلب مباشرة.',
        'stages' => [
            'applied' => 'تم التقديم',
            'review' => 'قيد المراجعة',
            'interview' => 'مقابلة',
            'offer' => 'عرض وظيفي',
        ],
        'role_details' => 'تفاصيل الوظيفة',
        'description_unavailable' => 'لم يتم توفير وصف للوظيفة.',
        'responsibilities' => 'المسؤوليات',
        'requirements' => 'المتطلبات',
        'benefits' => 'المزايا',
        'core_skills' => 'المهارات',
        'company_overview' => 'نبذة عن الشركة',
        'company_name_unavailable' => 'معلومات الشركة غير متاحة',
        'company_overview_unavailable' => 'لم يتم توفير نبذة عن الشركة.',
        'industry' => 'المجال',
        'hiring_contact' => 'مسؤول التوظيف',
        'contact_hiring_team' => 'تواصل مع فريق التوظيف',
        'contact_unavailable' => 'بيانات التواصل غير متاحة لهذه الوظيفة.',
    ],

    'eligibility' => [
        'restricted_title' => 'التقديم غير متاح',
        'restricted_generic' => 'لست مؤهلاً للتقديم على هذا الدور في المعاينة الحالية.',
        'authority_note' => 'معاينة الأهلية للعرض فقط. تبقى صلاحيات الخادم مطلوبة.',
        'open_membership' => 'فتح العضوية →',
        'nomination_hint' => 'اطلب من شركتك ترشيحك لهذا الدور، أو بدّل سيناريو الترشيح التجريبي.',
        'job_meta' => ':visibility · :mode',
        'apply_blocked_heading' => 'لا يمكنك متابعة هذا التقديم',
        'apply_blocked_body' => 'قواعد الأهلية لهذا الدور تمنع مسار التقديم في المعاينة الحالية.',
        'back_discover' => 'العودة إلى استكشاف الوظائف',
        'unavailable' => 'غير متاح',
        'reasons' => [
            'job_closed' => 'هذا الدور مغلق ولا يقبل طلبات.',
            'job_draft' => 'هذا الطلب ما زال مسودة.',
            'employee_public_blocked' => 'لا يمكن للموظفين المعتمدين التقديم على الوظائف العامة لشركتهم.',
            'membership_pending' => 'طلب عضوية شركتك ما زال قيد المراجعة. تبقى الأدوار الخاصة بالموظفين غير متاحة حتى الموافقة.',
            'membership_required' => 'هذا الدور الخاص مرئي لموظفي الشركة المعتمدين فقط.',
            'nomination_required' => 'أنت عضو معتمد، لكن هذا الدور بالترشيح فقط. يلزم ترشيح من شركتك للتقديم.',
            'nomination_membership_required' => 'دور الترشيح فقط يتطلب عضوية شركة معتمدة أولاً.',
            'nomination_membership_pending' => 'يجب الموافقة على العضوية قبل أن يفتح الترشيح هذا الدور.',
            'unknown' => 'تعذّر تحديد الأهلية في هذه المعاينة.',
        ],
    ],

    'scenario' => [
        'heading' => 'سيناريوهات أهلية تجريبية',
        'intro' => 'بدّل سياق العضوية والترشيح لمعاينات الوظائف الخاصة. ليست صلاحية حقيقية.',
        'public_default' => 'عامة / غير عضو',
        'approved_member' => 'عضو معتمد',
        'pending_member' => 'عضو معلّق',
        'nominated_member' => 'معتمد + مرشّح',
        'current' => 'الحالي: :scenario',
    ],
];
