https://github.com/chriskacerguis/codeigniter-restserver

GET : http://183.82.249.181:7281/dataware/security/modes
PUT : http://localhost/dataware/security/mode_activate?security_mode=1
PUT : http://localhost/dataware/security/mode_deactivate



GET 
http://localhost/api/rooms/list


POST
http://localhost/api/rooms/create
http://localhost/api/rooms/update/<room_id>

	{
        "room_name": "my test room",
        "floor_id": "1",
        "description": "test create room",
        "image_id": "1"
    }


DELETE
 http://localhost/api/rooms/delete/<room_id>

