<table>
    <thead>
        <tr>
            <th>Facility Name</th>
            <th>Average Monthly kWh</th>
            <th>Floor Area (sqm)</th>
            <th>EUI (kWh/sqm)</th>
            <th>Efficiency Band</th>
            <th>Months Included</th>
            <th>Latest Period</th>
            <th>Last Completed Maintenance</th>
            <th>Action Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($efficiencyRows as $row)
            <tr>
                <td>{{ $row['facility'] }}</td>
                <td>{{ $row['avg_monthly_kwh'] }}</td>
                <td>{{ $row['floor_area'] }}</td>
                <td>{{ $row['eui'] }}</td>
                <td>{{ $row['rating'] }}</td>
                <td>{{ $row['months_count'] }}</td>
                <td>{{ $row['latest_period'] }}</td>
                <td>{{ $row['last_audit'] }}</td>
                <td>{{ $row['maintenance_status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
