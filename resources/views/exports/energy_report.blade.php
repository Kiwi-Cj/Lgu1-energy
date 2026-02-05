<table>
    <thead>
        <tr>
            <th>Facility</th>
            <th>Month</th>
            <th>Actual kWh</th>
            <th>Baseline kWh</th>
            <th>Variance</th>
            <th>Trend</th>
        </tr>
    </thead>
    <tbody>
        @foreach($energyRows as $row)
            <tr>
                <td>{{ $row['facility'] }}</td>
                <td>{{ $row['month'] }}</td>
                <td>{{ $row['actual_kwh'] }}</td>
                <td>{{ $row['baseline_kwh'] }}</td>
                <td>{{ $row['variance'] }}</td>
                <td>{{ $row['trend'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
