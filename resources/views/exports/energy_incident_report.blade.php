<table>
    <thead>
        <tr>
            <th>Facility</th>
            <th>Month/Year</th>
            <th>Date Detected</th>
            <th>Status</th>
            <th>Severity</th>
            <th>Deviation</th>
            <th>Description</th>
            <th>Probable Cause</th>
            <th>Immediate Action</th>
            <th>Resolution</th>
            <th>Preventive Recommendation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($incidentRows as $row)
            <tr>
                <td>{{ $row['facility'] }}</td>
                <td>{{ $row['period'] }}</td>
                <td>{{ $row['date_detected'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['severity'] }}</td>
                <td>{{ $row['deviation'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td>{{ $row['probable_cause'] }}</td>
                <td>{{ $row['immediate_action'] }}</td>
                <td>{{ $row['resolution'] }}</td>
                <td>{{ $row['preventive_recommendation'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
