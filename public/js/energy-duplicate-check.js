// JS to check for duplicate energy record before enabling Add button
// Requires jQuery (or use fetch if you prefer vanilla JS)
document.addEventListener('DOMContentLoaded', function() {
    var facility = document.getElementById('facility_id');
    var month = document.getElementById('month');
    var year = document.getElementById('year');
    var submitBtn = document.querySelector('button[type="submit"]');
    var form = document.querySelector('form');
    var duplicateMsg = document.createElement('div');
    duplicateMsg.style.color = '#e11d48';
    duplicateMsg.style.fontWeight = '600';
    duplicateMsg.style.margin = '8px 0 0 0';
    duplicateMsg.style.display = 'none';
    duplicateMsg.textContent = 'Energy record for this facility and month/year already exists.';
    form.insertBefore(duplicateMsg, submitBtn);

    function checkDuplicate() {
        var f = facility.value;
        var m = month.value;
        var y = year.value;
        if (!f || !m || !y) {
            submitBtn.disabled = false;
            duplicateMsg.style.display = 'none';
            return;
        }
        fetch(`/modules/energy/check-duplicate?facility_id=${f}&month=${m}&year=${y}`)
            .then(r => r.json())
            .then(data => {
                if (data.exists) {
                    submitBtn.disabled = true;
                    duplicateMsg.style.display = 'block';
                } else {
                    submitBtn.disabled = false;
                    duplicateMsg.style.display = 'none';
                }
            });
    }
    facility.addEventListener('change', checkDuplicate);
    month.addEventListener('change', checkDuplicate);
    year.addEventListener('change', checkDuplicate);
});
