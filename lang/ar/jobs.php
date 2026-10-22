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
