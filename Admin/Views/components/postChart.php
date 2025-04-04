<div>
    <h4>Post Activity</h4>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center mb-3">
            <label for="postActivityRange" class="me-2">Select Time Range:</label>
            <select id="postActivityRange" class="form-select w-auto" data-profileId="<?= $profileId ?>">
                <option value="week">Week</option>
                <option value="month">Month</option>
                <option value="6months">6 Months</option>
                <option value="1year">1 Year</option>
                <option value="5years">5 Years</option>
            </select>
        </div>
        <div class="d-flex align-items-center mb-3">
            <label class="pe-1">Total Posts:</label>
            <label id="total-number-of-posts"></label>
        </div>
    </div>
    <canvas id="postActivityChart"></canvas>
</div>