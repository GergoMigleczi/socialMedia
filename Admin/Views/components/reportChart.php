<div>
    <h4 class="mt-4">Report Activity</h4>
    <div class="d-flex align-items-center mb-3">
        <label for="reportActivityRange" class="me-2">Select Time Range:</label>
        <select id="reportActivityRange" class="form-select w-auto" data-profileId="<?= $profileId ?>">
            <option value="week">Week</option>
            <option value="month">Month</option>
            <option value="6months">6 Months</option>
            <option value="1year">1 Year</option>
            <option value="5years">5 Years</option>
        </select>
    </div>
    <canvas id="reportActivityChart"></canvas>
</div>