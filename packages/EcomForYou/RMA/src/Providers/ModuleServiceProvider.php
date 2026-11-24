<?php

namespace EcomForYou\RMA\Providers;

use EcomForYou\RMA\Models\RMA;
use EcomForYou\RMA\Models\RMAAdditionalField;
use EcomForYou\RMA\Models\RMACustomField;
use EcomForYou\RMA\Models\RMACustomFieldOption;
use EcomForYou\RMA\Models\RMAImage;
use EcomForYou\RMA\Models\RMAItem;
use EcomForYou\RMA\Models\RMAMessage;
use EcomForYou\RMA\Models\RMAReason;
use EcomForYou\RMA\Models\RMAReasonResolution;
use EcomForYou\RMA\Models\RMARule;
use EcomForYou\RMA\Models\RMAStatus;
use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Define the models provided by this module
     */
    protected $models = [
        RMA::class,
        RMAAdditionalField::class,
        RMACustomField::class,
        RMACustomFieldOption::class,
        RMAImage::class,
        RMAItem::class,
        RMAMessage::class,
        RMAReason::class,
        RMAReasonResolution::class,
        RMARule::class,
        RMAStatus::class,
    ];
}
