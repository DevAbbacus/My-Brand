<fieldset class="inline-edit-col-center">
    <div class="inline-edit-col">
        <span class="title inline-edit-plans-label"><?php _e( 'Set Access', 'yith-woocommerce-membership' ) ?></span>
        <ul class="plans-checklist cat-checklist product_cat-checklist">
            <?php

            $plans = YITH_WCMBS_Manager()->get_plans();
            if ( !empty( $plans ) ) {
                foreach ( $plans as $plan ) {
                    echo "<li id='plan-{$plan->get_id()}'><label class='selectit'><input value='{$plan->get_id()}'
                                                                                   name='_yith_wcmbs_restrict_access_plan[]'
                                                                                   id='in-plan-{$plan->get_id()}'
                                                                                   type='checkbox'>{$plan->get_name()}</label>";
                }
            }
            ?>
        </ul>
    </div>
</fieldset>