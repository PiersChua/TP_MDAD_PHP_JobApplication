<?php
require_once __DIR__ . "/../lib/db.php";

$db = Db::getInstance();

$sql = "
INSERT INTO users (fullName, email, password, phoneNumber, dateOfBirth, role, gender, race, nationality)
VALUES
    ('Alice Tan', 'alice@example.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234567', '1990-01-01', 'Job Seeker', 'Female', 'Chinese', 'Singaporean'),
    ('Admin User', 'admin@example.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234568', '1985-06-15', 'Admin', 'Male', 'Others', 'Singaporean'),
    ('Agency Admin 1', 'admin1@agency1.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234569', '1980-03-22', 'Agency Admin', 'Male', 'Indian', 'Singaporean'),
    ('Agency Admin 2', 'admin2@agency2.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234570', '1990-05-14', 'Agency Admin', 'Female', 'Malay', 'Singaporean'),
    ('Agency Admin 3', 'admin3@agency3.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234571', '1988-08-30', 'Agency Admin', 'Male', 'Chinese', 'PR'),
    ('Agency Admin 4', 'admin4@agency4.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234572', '1979-11-20', 'Agency Admin', 'Female', 'Others', 'Singaporean'),
    ('Agency Admin 5', 'admin5@agency5.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234573', '1993-03-10', 'Agency Admin', 'Male', 'Indian', 'PR'),
    ('Agent 1', 'agent1@agency1.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234574', '1995-01-01', 'Agent', 'Male', 'Chinese', 'Singaporean'),
    ('Agent 2', 'agent2@agency2.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234575', '1996-06-05', 'Agent', 'Female', 'Malay', 'PR'),
    ('Agent 3', 'agent3@agency3.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234576', '1992-10-12', 'Agent', 'Male', 'Indian', 'Singaporean'),
    ('Agent 4', 'agent4@agency4.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234577', '1989-03-25', 'Agent', 'Female', 'Others', 'Singaporean'),
    ('Agent 5', 'agent5@agency5.com', '$2y$10$8EYQXM02gxCztFU0jdLQMOioBlJx3sobC70WetfxIjxNdgPPlxTEe', '81234578', '1987-09-15', 'Agent', 'Male', 'Chinese', 'PR');

INSERT INTO agencies (name, email, phoneNumber, address, userId)
VALUES
    ('Global Talent Agency', 'contact@globaltalent.sg', '68765432', '123 Orchard Road', (SELECT userId FROM users WHERE email = 'admin1@agency1.com')),
    ('Premier Recruitment SG', 'contact@premierrecruitment.sg', '68765433', '456 Marina Bay', (SELECT userId FROM users WHERE email = 'admin2@agency2.com')),
    ('Elite Workforce Solutions', 'contact@eliteworkforce.sg', '68765434', '789 Tanjong Pagar', (SELECT userId FROM users WHERE email = 'admin3@agency3.com')),
    ('Strategic Hiring Partners', 'contact@strategichiring.sg', '68765435', '321 Raffles Place', (SELECT userId FROM users WHERE email = 'admin4@agency4.com')),
    ('Nationwide Staffing', 'contact@nationwidestaffing.sg', '68765436', '654 Bukit Timah', (SELECT userId FROM users WHERE email = 'admin5@agency5.com'));

UPDATE users
SET agencyId = (SELECT agencyId FROM agencies WHERE name = 'Global Talent Agency') WHERE email = 'agent1@agency1.com';
UPDATE users
SET agencyId = (SELECT agencyId FROM agencies WHERE name = 'Premier Recruitment SG') WHERE email = 'agent2@agency2.com';
UPDATE users
SET agencyId = (SELECT agencyId FROM agencies WHERE name = 'Elite Workforce Solutions') WHERE email = 'agent3@agency3.com';
UPDATE users
SET agencyId = (SELECT agencyId FROM agencies WHERE name = 'Strategic Hiring Partners') WHERE email = 'agent4@agency4.com';
UPDATE users
SET agencyId = (SELECT agencyId FROM agencies WHERE name = 'Nationwide Staffing') WHERE email = 'agent5@agency5.com';


INSERT INTO jobs (position, responsibilities, description, location, schedule, organisation, partTimeSalary, fullTimeSalary, userId)
VALUES

('Data Analyst', 
'Analyze complex data sets to identify trends and insights that inform business decisions.\n\nDevelop and maintain databases, data systems, and data processes to support data-driven decision-making.\n\nCreate data visualizations and reports to communicate findings to stakeholders.\n\nCollaborate with cross-functional teams to design and implement data-driven solutions.\n\nStay up-to-date with industry trends and emerging technologies in data analysis.', 
'Join a dynamic team of data professionals dedicated to driving business growth through data-driven insights.\n\nThis role offers the opportunity to work on high-impact projects that shape business strategy.\n\nCollaborate with a talented team of professionals passionate about data analysis and business intelligence.', 
'123 Anson Road', 
'Mon-Fri 9am-6pm', 
'Data Insights Inc.', 
NULL, 
6000.00, 
(SELECT userId FROM users WHERE email = 'agent1@agency1.com')),
('Marketing Manager', 
'Develop and execute comprehensive marketing strategies to drive business growth.\n\nCollaborate with cross-functional teams to design and implement marketing campaigns.\n\nAnalyze market trends and competitor activity to inform marketing strategies.\n\nManage and optimize marketing budgets to achieve maximum ROI.\n\nDevelop and maintain relationships with key stakeholders, including customers, partners, and vendors.', 
'Join a high-performing marketing team dedicated to driving business growth through innovative marketing strategies.\n\nThis role offers the opportunity to work on high-impact projects that shape business strategy.\n\nCollaborate with a talented team of marketing professionals passionate about delivering exceptional results.', 
'123 Anson Road', 
'Mon-Fri 9am-6pm', 
'Marketing Masters', 
NULL, 
7000.00, 
(SELECT userId FROM users WHERE email = 'agent1@agency1.com')),
('Software Engineer', 
'Design, develop, and test software applications to meet business requirements.\n\nCollaborate with cross-functional teams to design and implement software solutions.\n\nParticipate in code reviews to ensure high-quality software development.\n\nTroubleshoot and resolve software issues to ensure system stability and performance.\n\nStay up-to-date with industry trends and emerging technologies in software development.', 
'Join a team of talented software engineers dedicated to delivering high-quality software solutions.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in software development.', 
'123 Anson Road', 
'Mon-Fri 9am-6pm', 
'Software Solutions Inc.', 
NULL, 
8000.00, 
(SELECT userId FROM users WHERE email = 'agent1@agency1.com')),
('UX Designer', 
'Design user-centered experiences that meet business requirements and user needs.\n\nCollaborate with cross-functional teams to design and implement user experiences.\n\nConduct user research to inform design decisions and validate design solutions.\n\nDevelop and maintain design documentation and design systems.\n\nStay up-to-date with industry trends and emerging technologies in UX design.', 
'Join a team of talented UX designers dedicated to delivering exceptional user experiences.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in UX design.', 
'123 Anson Road', 
'Mon-Fri 9am-6pm', 
'UX Design Studio', 
NULL, 
7500.00, 
(SELECT userId FROM users WHERE email = 'agent1@agency1.com')),
('DevOps Engineer', 
'Design, implement, and maintain infrastructure and tools to support software development and deployment.\n\nCollaborate with cross-functional teams to design and implement infrastructure solutions.\n\nParticipate in on-call rotations to ensure system stability and performance.\n\nTroubleshoot and resolve infrastructure issues to ensure system uptime and performance.\n\nStay up-to-date with industry trends and emerging technologies in DevOps.', 
'Join a team of talented DevOps engineers dedicated to delivering high-quality infrastructure solutions.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in DevOps.', 
'123 Anson Road', 
'Mon-Fri 9am-6pm', 
'DevOps Solutions Inc.', 
NULL, 
8500.00, 
(SELECT userId FROM users WHERE email = 'agent1@agency1.com')),

('Product Manager',
'Develop and execute comprehensive product strategies to drive business growth.\n\nCollaborate with cross-functional teams to design and implement product solutions.\n\nAnalyze market trends and competitor activity to inform product strategies.\n\nManage and optimize product budgets to achieve maximum ROI.\n\nDevelop and maintain relationships with key stakeholders, including customers, partners, and vendors.',
'Join a high-performing product team dedicated to driving business growth through innovative product solutions.\n\nThis role offers the opportunity to work on high-impact projects that shape business strategy.\n\nCollaborate with a talented team of product professionals passionate about delivering exceptional results.',
'456 Orchard Road',
'Mon-Fri 9am-6pm',
'Product Prodigy',
NULL,
7500.00,
(SELECT userId FROM users WHERE email = 'agent2@agency2.com')),
('Data Scientist',
'Develop and implement advanced data models and algorithms to drive business insights.\n\nCollaborate with cross-functional teams to design and implement data-driven solutions.\n\nAnalyze complex data sets to identify trends and insights that inform business decisions.\n\nDevelop and maintain databases, data systems, and data processes to support data-driven decision-making.\n\nStay up-to-date with industry trends and emerging technologies in data science.',
'Join a team of talented data scientists dedicated to driving business growth through data-driven insights.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in data science.',
'456 Orchard Road',
'Mon-Fri 9am-6pm',
'Data Science Inc.',
NULL,
8000.00,
(SELECT userId FROM users WHERE email = 'agent2@agency2.com')),
('Cybersecurity Specialist',
'Design, implement, and maintain robust cybersecurity systems to protect against threats.\n\nCollaborate with cross-functional teams to identify and mitigate cybersecurity risks.\n\nDevelop and implement incident response plans to ensure business continuity.\n\nConduct penetration testing and vulnerability assessments to identify security weaknesses.\n\nStay up-to-date with industry trends and emerging technologies in cybersecurity.',
'Join a team of talented cybersecurity specialists dedicated to protecting against cyber threats.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in cybersecurity.',
'456 Orchard Road',
'Mon-Fri 9am-6pm',
'Cybersecurity Solutions',
NULL,
8500.00,
(SELECT userId FROM users WHERE email = 'agent2@agency2.com')),
('Full Stack Developer',
'Design, develop, and test full-stack applications to meet business requirements.\n\nCollaborate with cross-functional teams to design and implement software solutions.\n\nParticipate in code reviews to ensure high-quality software development.\n\nTroubleshoot and resolve software issues to ensure system stability and performance.\n\nStay up-to-date with industry trends and emerging technologies in software development.',
'Join a team of talented full-stack developers dedicated to delivering high-quality software solutions.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in software development.',
'456 Orchard Road',
'Mon-Fri 9am-6pm',
'Full Stack Solutions',
NULL,
8000.00,
(SELECT userId FROM users WHERE email = 'agent2@agency2.com')),
('Artificial Intelligence Engineer',
'Design, develop, and test AI models and algorithms to drive business insights.\n\nCollaborate with cross-functional teams to design and implement AI-driven solutions.\n\nAnalyze complex data sets to identify trends and insights that inform business decisions.\n\nDevelop and maintain databases, data systems, and data processes to support AI-driven decision-making.\n\nStay up-to-date with industry trends and emerging technologies in AI.',
'Join a team of talented AI engineers dedicated to driving business growth through AI-driven insights.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in AI.',
'456 Orchard Road',
'Mon-Fri 9am-6pm',
'AI Solutions Inc.',
NULL,
9000.00,
(SELECT userId FROM users WHERE email = 'agent2@agency2.com')),

('Interior Designer',
'Develop creative interior solutions that balance functionality and aesthetics, tailored to client specifications.\n\nCollaborate with clients to understand their preferences and translate them into innovative designs.\n\nSource and recommend materials, furniture, and accessories to achieve desired themes.\n\nWork closely with contractors and suppliers to ensure the execution of designs meets quality standards.\n\nCreate detailed project plans and timelines to keep projects on schedule and within budget.',
'Join a visionary team delivering bespoke interior design solutions in dynamic settings.\n\nThis role offers a chance to work on exciting projects that redefine modern spaces.\n\nYou will collaborate with a passionate team dedicated to excellence and client satisfaction.',
'789 Tanjong Pagar',
'Mon-Fri 9am-6pm',
'Interior Home SG',
45.00,
8000.00,
(SELECT userId FROM users WHERE email = 'agent3@agency3.com')),
('UX Researcher',
'Conduct user research to inform design decisions and validate design solutions.\n\nDevelop and maintain research plans and protocols to ensure high-quality research outcomes.\n\nCollaborate with cross-functional teams to design and implement user-centered design solutions.\n\nAnalyze and interpret research data to identify trends and insights that inform design decisions.\n\nDevelop and maintain research documentation and reports to support design decisions.',
'Join a team of talented UX researchers dedicated to delivering exceptional user experiences.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in UX research.',
'789 Tanjong Pagar',
'Mon-Fri 9am-6pm',
'UX Research Studio',
NULL,
7000.00,
(SELECT userId FROM users WHERE email = 'agent3@agency3.com')),
('Digital Marketing Specialist',
'Develop and execute comprehensive digital marketing strategies to drive business growth.\n\nCollaborate with cross-functional teams to design and implement digital marketing campaigns.\n\nAnalyze digital marketing metrics to inform marketing strategies and optimize campaign performance.\n\nManage and optimize digital marketing budgets to achieve maximum ROI.\n\nDevelop and maintain relationships with key stakeholders, including customers, partners, and vendors.',
'Join a high-performing digital marketing team dedicated to driving business growth through innovative digital marketing strategies.\n\nThis role offers the opportunity to work on high-impact projects that shape business strategy.\n\nCollaborate with a talented team of digital marketing professionals passionate about delivering exceptional results.',
'789 Tanjong Pagar',
'Mon-Fri 9am-6pm',
'Digital Marketing Agency',
NULL,
6500.00,
(SELECT userId FROM users WHERE email = 'agent3@agency3.com')),
('Business Analyst',
'Develop and maintain business cases to support business decisions.\n\nCollaborate with cross-functional teams to design and implement business solutions.\n\nAnalyze business metrics to inform business strategies and optimize solution performance.\n\nManage and optimize business budgets to achieve maximum ROI.\n\nDevelop and maintain relationships with key stakeholders, including customers, partners, and vendors.',
'Join a team of talented business analysts dedicated to driving business growth through data-driven insights.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in business analysis.',
'789 Tanjong Pagar',
'Mon-Fri 9am-6pm',
'Business Analysis Inc.',
NULL,
6000.00,
(SELECT userId FROM users WHERE email = 'agent3@agency3.com')),
('Cloud Engineer',
'Design, develop, and test cloud-based systems to meet business requirements.\n\nCollaborate with cross-functional teams to design and implement cloud-based solutions.\n\nParticipate in on-call rotations to ensure system stability and performance.\n\nTroubleshoot and resolve system issues to ensure system uptime and performance.\n\nStay up-to-date with industry trends and emerging technologies in cloud computing.',
'Join a team of talented cloud engineers dedicated to delivering high-quality cloud-based solutions.\n\nThis role offers the opportunity to work on challenging projects that shape business strategy.\n\nCollaborate with a passionate team of professionals committed to excellence in cloud engineering.',
'789 Tanjong Pagar',
'Mon-Fri 9am-6pm',
'Cloud Engineering Inc.',
NULL,
8500.00,
(SELECT userId FROM users WHERE email = 'agent3@agency3.com')),

('FinTech Analyst',
'Analyze financial data, identify trends, and design tools that drive data-driven decision-making in fintech innovation.\n\nCollaborate with product teams to enhance financial solutions tailored to client needs.\n\nDevelop models and simulations to forecast market behavior and assess risks.\n\nProvide insights into emerging fintech trends and recommend actionable strategies for growth.\n\nPrepare detailed reports and presentations for stakeholders and clients.',
'Work on pioneering projects that redefine financial ecosystems.\n\nThis is an opportunity to be part of a trailblazing team shaping the future of finance.\n\nYour contributions will directly impact the fintech landscape in Singapore and beyond.',
'321 Raffles Place',
'Mon-Fri 9am-6pm',
'Venti Technologies Corporation',
NULL,
8000.00,
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),
('Retail Assistant',
'Assist customers with purchases and provide excellent customer service.\n\nMaintain store displays and ensure a clean and organized store environment.\n\nProcess transactions and handle customer payments.\n\nRestock shelves and maintain inventory levels.\n\nCollaborate with colleagues to achieve sales targets and store goals.',
'Join a dynamic team of retail professionals dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting retail environment.\n\nCollaborate with a passionate team of professionals committed to excellence in customer service.',
'123 Orchard Road',
'Mon-Sun 10am-10pm',
'Retail Inc.',
15.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),
('Cashier',
'Process transactions and handle customer payments.\n\nMaintain a clean and organized workspace.\n\nProvide excellent customer service and respond to customer inquiries.\n\nCollaborate with colleagues to achieve sales targets and store goals.\n\nMaintain accuracy and attention to detail when handling cash and operating the point-of-sale system.',
'Join a team of friendly and efficient cashiers dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting retail environment.\n\nCollaborate with a passionate team of professionals committed to excellence in customer service.',
'123 Orchard Road',
'Mon-Sun 10am-10pm',
'Retail Inc.',
12.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),
('Packer',
'Pack and prepare orders for shipping.\n\nMaintain a clean and organized workspace.\n\nCollaborate with colleagues to achieve packing and shipping targets.\n\nEnsure accuracy and attention to detail when packing orders.\n\nUse packing materials efficiently to minimize waste.',
'Join a team of efficient and detail-oriented packers dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting logistics environment.\n\nCollaborate with a passionate team of professionals committed to excellence in packing and shipping.',
'456 Changi Road',
'Mon-Fri 9am-6pm',
'Logistics Inc.',
10.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),
('Delivery Driver',
'Deliver packages to customers in a safe and timely manner.\n\nMaintain a clean and organized vehicle.\n\nCollaborate with colleagues to achieve delivery targets.\n\nHandle customer service issues related to deliveries.\n\nPerform other delivery-related tasks as required.',
'Join a dynamic team of delivery professionals dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting logistics environment.\n\nCollaborate with a passionate team of professionals committed to excellence in delivery services.',
'321 Changi Road',
'Mon-Sun 10am-6pm',
'Delivery Services Inc.',
15.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),
('Warehouse Worker',
'Receive, organize, and ship out inventory in a warehouse environment.\n\nMaintain a clean and organized workspace.\n\nCollaborate with colleagues to achieve warehouse targets.\n\nHandle inventory accurately and efficiently.\n\nPerform other warehouse-related tasks as required.',
'Join a dynamic team of warehouse professionals dedicated to delivering exceptional logistics services.\n\nThis role offers the opportunity to work in a fast-paced and exciting warehouse environment.\n\nCollaborate with a passionate team of professionals committed to excellence in warehouse management.',
'456 Pasir Panjang',
'Mon-Fri 9am-6pm',
'Warehouse Logistics Inc.',
12.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),

('Teacher',
'Design and deliver engaging lesson plans that inspire curiosity and foster critical thinking among students.\n\nEvaluate student progress and provide feedback to support their academic development.\n\nIncorporate technology and innovative teaching methods to enhance learning experiences.\n\nCreate a positive and inclusive classroom environment that encourages participation.\n\nCollaborate with colleagues to develop and refine curriculum materials.',
'Be part of an educational movement empowering young minds to achieve their fullest potential.\n\nThis role provides the opportunity to shape the future by nurturing the next generation of leaders.\n\nJoin a dedicated team committed to academic excellence and holistic education.',
'654 Bukit Timah',
'Mon-Fri 8am-4pm',
'My First Skool',
NULL,
5000.00,
(SELECT userId FROM users WHERE email = 'agent5@agency5.com')),
('Retail Sales Associate',
'Assist customers with purchases and provide excellent customer service.\n\nMaintain store displays and ensure a clean and organized store environment.\n\nProcess transactions and handle customer payments.\n\nRestock shelves and maintain inventory levels.\n\nCollaborate with colleagues to achieve sales targets and store goals.',
'Join a dynamic team of retail professionals dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting retail environment.\n\nCollaborate with a passionate team of professionals committed to excellence in customer service.',
'789 Orchard Road',
'Mon-Sun 10am-10pm',
'Retail Inc.',
15.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent5@agency5.com')),
('Food and Beverage Server',
'Provide exceptional customer service to guests in a fast-paced food and beverage environment.\n\nTake orders and serve food and beverages to guests.\n\nMaintain a clean and organized workspace.\n\nCollaborate with colleagues to achieve sales targets and provide excellent customer service.\n\nHandle cash and credit transactions accurately and efficiently.',
'Join a dynamic team of food and beverage professionals dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting food and beverage environment.\n\nCollaborate with a passionate team of professionals committed to excellence in customer service.',
'901 Sentosa Island',
'Mon-Sun 10am-10pm',
'Food and Beverage Inc.',
12.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent5@agency5.com')),
('Customer Service Representative',
'Provide exceptional customer service to clients via phone, email, and chat.\n\nRespond to customer inquiries and resolve customer complaints.\n\nMaintain a clean and organized workspace.\n\nCollaborate with colleagues to achieve sales targets and provide excellent customer service.\n\nHandle customer data accurately and confidentially.',
'Join a dynamic team of customer service professionals dedicated to delivering exceptional customer experiences.\n\nThis role offers the opportunity to work in a fast-paced and exciting customer service environment.\n\nCollaborate with a passionate team of professionals committed to excellence in customer service.',
'456 Tampines Avenue',
'Mon-Fri 9am-6pm',
'Customer Service Inc.',
10.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent5@agency5.com')),
('Data Entry Clerk',
'Enter data accurately and efficiently into computer systems.\n\nMaintain a clean and organized workspace.\n\nCollaborate with colleagues to achieve data entry targets.\n\nHandle data accurately and confidentially.\n\nPerform other administrative tasks as required.',
'Join a dynamic team of administrative professionals dedicated to delivering exceptional support services.\n\nThis role offers the opportunity to work in a fast-paced and exciting administrative environment.\n\nCollaborate with a passionate team of professionals committed to excellence in administrative support.',
'789 Woodlands Avenue',
'Mon-Fri 9am-6pm',
'Administrative Support Inc.',
9.00,
NULL,
(SELECT userId FROM users WHERE email = 'agent5@agency5.com'));

";

if ($db->getConnection()) {
    if (mysqli_multi_query($db->getConnection(), $sql)) {
        echo "Data seeded successfully!";
    } else {
        echo "Error creating tables: " . mysqli_error($db->getConnection());
    }
    $db->close();
} else {
    echo "Connection failed: " . mysqli_connect_error();
}
