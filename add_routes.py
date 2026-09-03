import re

file_path = 'routes/web.php'
with open(file_path, 'r') as f:
    content = f.read()

new_routes = """
        $router->get('reports/patient-wise', 'Api\\V1\\ReportController@patientWise');
        $router->get('reports/therapist-wise', 'Api\\V1\\ReportController@therapistWise');
        $router->get('reports/therapy-wise', 'Api\\V1\\ReportController@therapyWise');
"""

content = content.replace(
    "        $router->get('reports/therapist-performance', 'Api\\V1\\ReportController@therapistPerformance');",
    "        $router->get('reports/therapist-performance', 'Api\\V1\\ReportController@therapistPerformance');" + new_routes
)

with open(file_path, 'w') as f:
    f.write(content)
