
USE task_manager;


INSERT INTO user_account (user_id, user_name, email_address, password_hash, user_type) VALUES
(1, 'John Admin', 'john@example.com', 'dummy_hash_1', 'admin'),
(2, 'Jane Manager', 'jane@example.com', 'dummy_hash_2', 'member'),
(3, 'Bob Developer', 'bob@example.com', 'dummy_hash_3', 'member');


INSERT INTO organization (organization_id, name, description, created_by) VALUES
(1, 'Tech Solutions Inc', 'Main software development organization', 1),
(2, 'Marketing Team', 'Internal marketing department', 2);


INSERT INTO organization_user (organization_user_id, organization_id, user_id, role_in_org) VALUES
(1, 1, 1, 'owner'),   
(2, 1, 2, 'admin'), 
(3, 1, 3, 'member'),  
(4, 2, 2, 'owner'),   
(5, 2, 1, 'member');  


INSERT INTO project (project_id, name, description, status, priority, visibility, user_id, organization_id) VALUES
(1, 'Website Redesign', 'Revamp company website with modern UI', 'active', 'high', 'private', 1, 1),
(2, 'Mobile App', 'Develop iOS and Android apps', 'planned', 'medium', 'private', 2, 1),
(3, 'Q4 Marketing', 'Year-end marketing campaign', 'active', 'high', 'private', 2, 2);


INSERT INTO user_project (user_project_id, project_id, user_id, role_in_project) VALUES
(1, 1, 1, 'owner'),     
(2, 1, 2, 'contributor'), 
(3, 1, 3, 'contributor'), 
(4, 2, 2, 'owner'),     
(5, 2, 3, 'contributor'); 


INSERT INTO tasks (task_id, title, description, status, priority, project_id) VALUES
(1, 'Design Homepage', 'Create mockups for new homepage', 'in_progress', 'high', 1),
(2, 'User Authentication', 'Implement login/register system', 'todo', 'high', 1),
(3, 'Mobile Responsive', 'Ensure site works on all devices', 'todo', 'medium', 1);


INSERT INTO task_assignee (task_assignee_id, task_id, user_id, role) VALUES
(1, 1, 2, 'assignee'), 
(2, 1, 1, 'reviewer'),  
(3, 2, 3, 'assignee'), 
(4, 3, 2, 'assignee'); 


INSERT INTO todo (todo_id, todo_name, list_type, todo_data) VALUES
(1, 'Review PRs', 'work', 'Check pending pull requests'),
(2, 'Team Meeting', 'work', 'Prepare agenda for tomorrow'),
(3, 'Grocery List', 'personal', 'Milk, Bread, Eggs');


INSERT INTO todo_user (todo_user_id, todo_id, user_id) VALUES
(1, 1, 1), 
(2, 2, 2), 
(3, 3, 3);  