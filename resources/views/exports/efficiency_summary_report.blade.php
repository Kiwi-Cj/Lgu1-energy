<table>
    <thead>
        <tr>
            <th>Facility Name</th>
            <th>EUI (kWh/sqm)</th>
            <th>Efficiency Rating</th>
            <th>Last Audit</th>
            <th>Maintenance Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($efficiencyRows as $row)
            <tr>
                <td>{{ $row['facility'] }}</td>
                <td>{{ $row['eui'] }}</td>
                <td>{{ $row['rating'] }}</td>
                <td>{{ $row['last_audit'] }}</td>
                <td>{{ $row['maintenance_status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
