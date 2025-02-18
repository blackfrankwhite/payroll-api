<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_prorated_adjustment_for_period;");
        
        DB::unprepared("
            CREATE FUNCTION calculate_prorated_adjustment_for_period(
                adjustment_amount DECIMAL(18,2),
                adjustment_start DATE,
                adjustment_end DATE,
                salary_id INT,
                daily_calculation_base ENUM('WORKING_DAYS','CALENDAR_DAYS')
            )
            RETURNS DECIMAL(18,2)
            DETERMINISTIC
            BEGIN
                DECLARE total_adjustment DECIMAL(18,6) DEFAULT 0;
                DECLARE current_month DATE;
                DECLARE month_start DATE;
                DECLARE month_end DATE;
                DECLARE full_month_days INT;
                DECLARE effective_days INT;
                DECLARE daily_rate DECIMAL(18,6);

                SET current_month = DATE_FORMAT(adjustment_start, '%Y-%m-01');

                WHILE current_month <= adjustment_end DO
                    SET month_start = current_month;
                    SET month_end = LAST_DAY(current_month);

                    IF month_start < adjustment_start THEN
                        SET month_start = adjustment_start;
                    END IF;
                    IF month_end > adjustment_end THEN
                        SET month_end = adjustment_end;
                    END IF;

                    IF daily_calculation_base = 'WORKING_DAYS' THEN
                        SET full_month_days = calculate_working_days_for_working_days(
                            DATE_FORMAT(current_month, '%Y-%m-01'),
                            LAST_DAY(current_month),
                            salary_id
                        );
                        SET effective_days = calculate_working_days_for_working_days(
                            month_start,
                            month_end,
                            salary_id
                        );
                    ELSE
                        SET full_month_days = calculate_working_days_for_calendar_days(
                            DATE_FORMAT(current_month, '%Y-%m-01'),
                            LAST_DAY(current_month)
                        );
                        SET effective_days = calculate_working_days_for_calendar_days(
                            month_start,
                            month_end
                        );
                    END IF;


                    IF full_month_days > 0 THEN
                        SET daily_rate = adjustment_amount / full_month_days;
                        SET total_adjustment = total_adjustment + (daily_rate * effective_days);

                    END IF;

                    SET current_month = DATE_ADD(current_month, INTERVAL 1 MONTH);
                END WHILE;

                RETURN ROUND(total_adjustment, 2);
            END;
        ");
    }
    
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_prorated_adjustment_for_period;");
    }
};
