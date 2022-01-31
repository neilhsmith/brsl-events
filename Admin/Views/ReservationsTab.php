<?php

declare(strict_types=1);

namespace BRSL\Admin\Views;

use BRSL\Admin\Views\ReservationsListTable;

if (!defined('ABSPATH')) exit;

class ReservationsTab {

    public static function render(): void {
        $reservationsListTable = new ReservationsListTable();
        print_r($_POST);
        ?>
        <h3>Reservations</h3>
        <form method="post" id="filter-reservations" action="">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="sponsor_name">Sponsor</label>
                        </th>
                        <td>
                            <input name="sponsor_name" id="sponsor_name" type="text" placeholder="Sponsor" value="<?php echo $_POST['sponsor_name']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="lja_name">LJA</label>
                        </th>
                        <td>
                            <input name="lja_name" id="lja_name" type="text" placeholder="LJA" value="<?php echo $_POST['lja_name']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="lja_class">Class</label>
                        </th>
                        <td>
                            <select name="lja_class" id="lja_class">
                                <option value="">-</option>
                                <option value="brsl_senior" <?php echo $_POST['lja_class'] == 'brsl_senior' ? 'selected' : ''; ?>>Seniors</option>
                                <option value="brsl_junior" <?php echo $_POST['lja_class'] == 'brsl_junior' ? 'selected' : ''; ?>>Juniors</option>
                                <option value="brsl_sophomore" <?php echo $_POST['lja_class'] == 'brsl_sophomore' ? 'selected' : ''; ?>>Sophomores</option>
                                <option value="brsl_freshman" <?php echo $_POST['lja_class'] == 'brsl_freshman' ? 'selected' : ''; ?>>Freshmen</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Under 21
                        </th>
                        <td>
                            <fieldset>
                                <label>Yes</label>
                                <input type="checkbox" value="1" id="under_21_true" name="under_21_true" <?php echo $_POST['under_21_true'] === '1' ? 'checked' : ''; ?>>
                            </fieldset>
                            <fieldset>
                                <label>No</label>
                                <input type="checkbox" value="1" id="under_21_false" name="under_21_false" <?php echo $_POST['under_21_false'] === '1' ? 'checked' : ''; ?>>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Vegatarian Meal
                        </th>
                        <td>
                            <fieldset>
                                <label>Yes</label>
                                <input type="checkbox" value="1" id="vegan_meal_true" name="vegan_meal_true" <?php echo $_POST['vegan_meal_true'] === '1' ? 'checked' : ''; ?>>
                            </fieldset>
                            <fieldset>
                                <label>No</label>
                                <input type="checkbox" value="1" id="vegan_meal_false" name="vegan_meal_false" <?php echo $_POST['vegan_meal_false'] === '1' ? 'checked' : ''; ?>>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="button button-primary" >Filter</button>
            <input type="submit" name="download_reservations_csv" class="button button-secondary" value="Export" />
        </form>
        <?php
        $reservationsListTable->prepare_items();
        $reservationsListTable->display();
    }
    
}