<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // 1. Create calculate_working_days_for_working_days function
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_working_days_for_working_days;");
        DB::unprepared("
            CREATE FUNCTION calculate_working_days_for_working_days(
                start_date DATE,
                end_date DATE,
                salary_id INT
            )
            RETURNS INT
            DETERMINISTIC
            BEGIN
                -- TODO: Replace the following dummy implementation with your real logic.
                -- For now, this dummy version simply returns the difference in days plus 1.
                RETURN DATEDIFF(end_date, start_date) + 1;
            END;
        ");

        // 2. Create calculate_working_days_for_calendar_days function
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_working_days_for_calendar_days;");
        DB::unprepared("
            CREATE FUNCTION calculate_working_days_for_calendar_days(
                start_date DATE,
                end_date DATE
            )
            RETURNS INT
            DETERMINISTIC
            BEGIN
                -- TODO: Replace the following dummy implementation with your real logic.
                -- For now, this dummy version simply returns the difference in days plus 1.
                RETURN DATEDIFF(end_date, start_date) + 1;
            END;
        ");

        // 3. Create calculate_prorated_salary_for_period function
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_prorated_salary_for_period;");
        DB::unprepared("
            CREATE FUNCTION calculate_prorated_salary_for_period(
                base_salary DECIMAL(18,6), 
                start_date DATE, 
                end_date DATE, 
                salary_id INT,
                daily_salary_calculation_base ENUM('WORKING_DAYS','CALENDAR_DAYS')
            ) 
            RETURNS DECIMAL(18,2)
            DETERMINISTIC
            BEGIN
                DECLARE total_prorated DECIMAL(18,6) DEFAULT 0;
                DECLARE current_month DATE;
                DECLARE month_start DATE;
                DECLARE month_end DATE;
                DECLARE working_days_in_month INT;
                DECLARE days_worked_in_month INT;
                DECLARE daily_salary DECIMAL(18,6);

                -- Start from the first day of the month of the start_date.
                SET current_month = DATE_FORMAT(start_date, '%Y-%m-01');

                WHILE current_month <= end_date DO
                    SET month_start = current_month;
                    SET month_end = LAST_DAY(current_month);

                    -- Clamp the month boundaries to the given period.
                    IF month_start < start_date THEN
                        SET month_start = start_date;
                    END IF;
                    IF month_end > end_date THEN
                        SET month_end = end_date;
                    END IF;

                    IF daily_salary_calculation_base = 'WORKING_DAYS' THEN
                        SET working_days_in_month = calculate_working_days_for_working_days(
                            DATE_FORMAT(current_month, '%Y-%m-01'),
                            LAST_DAY(current_month),
                            salary_id
                        );
                        SET days_worked_in_month = calculate_working_days_for_working_days(
                            month_start,
                            month_end,
                            salary_id
                        );
                    ELSE
                        SET working_days_in_month = calculate_working_days_for_calendar_days(
                            DATE_FORMAT(current_month, '%Y-%m-01'),
                            LAST_DAY(current_month)
                        );
                        SET days_worked_in_month = calculate_working_days_for_calendar_days(
                            month_start,
                            month_end
                        );
                    END IF;

                    IF working_days_in_month > 0 THEN
                        SET daily_salary = ROUND(base_salary / working_days_in_month, 6);
                        SET total_prorated = total_prorated + (daily_salary * days_worked_in_month);
                    END IF;

                    SET current_month = DATE_ADD(current_month, INTERVAL 1 MONTH);
                END WHILE;

                RETURN ROUND(total_prorated, 2);
            END;
        ");

        // 4. Create calculate_salary_breakdown function
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_salary_breakdown;");
        DB::unprepared("
            CREATE FUNCTION calculate_salary_breakdown(
                prorated_salary DECIMAL(18,2),
                includes_income_tax BOOLEAN,
                includes_employee_pension BOOLEAN
            ) 
            RETURNS JSON
            DETERMINISTIC
            BEGIN
                DECLARE pension_co DECIMAL(5,4) DEFAULT 0.02;
                DECLARE income_tax_co DECIMAL(5,4) DEFAULT 0.196;
                DECLARE net_co DECIMAL(5,4) DEFAULT 0.784;
                DECLARE base_salary DECIMAL(18,6);
                DECLARE pension DECIMAL(18,6);
                DECLARE income_tax DECIMAL(18,6);
                DECLARE net DECIMAL(18,6);
                DECLARE result JSON;
                
                IF includes_income_tax AND includes_employee_pension THEN
                    SET base_salary = prorated_salary;
                ELSEIF includes_employee_pension THEN
                    SET base_salary = prorated_salary / (net_co + pension_co);
                ELSEIF includes_income_tax THEN
                    SET base_salary = prorated_salary / (net_co + income_tax_co);
                ELSE
                    SET base_salary = prorated_salary / net_co;
                END IF;
                
                SET pension = base_salary * pension_co;
                SET income_tax = base_salary * income_tax_co;
                SET net = base_salary * net_co;
                
                SET result = JSON_OBJECT(
                    'base', ROUND(base_salary, 2),
                    'pension', ROUND(pension, 2),
                    'income_tax', ROUND(income_tax, 2),
                    'net', ROUND(net, 2)
                );
                
                RETURN result;
            END;
        ");
    }

    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_working_days_for_working_days;");
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_working_days_for_calendar_days;");
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_prorated_salary_for_period;");
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_salary_breakdown;");
    }
};
