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
   ('Interior Designer', 
'Develop creative interior solutions that balance functionality and aesthetics, tailored to client specifications.\nCollaborate with clients to understand their preferences and translate them into innovative designs.\nSource and recommend materials, furniture, and accessories to achieve desired themes.\nWork closely with contractors and suppliers to ensure the execution of designs meets quality standards.\nCreate detailed project plans and timelines to keep projects on schedule and within budget.\nStay informed about the latest trends in interior design and incorporate them into client projects.\nConduct site visits to monitor progress and resolve any on-site challenges.\nProvide design presentations to clients, complete with 3D renderings and visual concepts.', 
'Join a visionary team delivering bespoke interior design solutions in dynamic settings.\nThis role offers a chance to work on exciting projects that redefine modern spaces.\nYou will collaborate with a passionate team dedicated to excellence and client satisfaction.', 
'789 Tanjong Pagar', 
'Mon-Fri 9am-6pm', 
'Interior Home SG', 
45.00, 
8000.00, 
(SELECT userId FROM users WHERE email = 'agent3@agency3.com')),

('FinTech Analyst', 
'Analyze financial data, identify trends, and design tools that drive data-driven decision-making in fintech innovation.\nCollaborate with product teams to enhance financial solutions tailored to client needs.\nDevelop models and simulations to forecast market behavior and assess risks.\nProvide insights into emerging fintech trends and recommend actionable strategies for growth.\nPrepare detailed reports and presentations for stakeholders and clients.\nParticipate in the design and testing of financial platforms to ensure functionality and efficiency.\nEnsure compliance with regulatory requirements and industry best practices.\nSupport the implementation of new tools and technologies to optimize business processes.', 
'Work on pioneering projects that redefine financial ecosystems.\nThis is an opportunity to be part of a trailblazing team shaping the future of finance.\nYour contributions will directly impact the fintech landscape in Singapore and beyond.', 
'321 Raffles Place', 
'Mon-Fri 9am-6pm', 
'Venti Technologies Corporation', 
NULL, 
8000.00, 
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),

('Blockchain Developer', 
'Develop, test, and deploy blockchain solutions that ensure secure, decentralized, and efficient operations.\nCollaborate with cross-functional teams to design and implement smart contracts.\nOptimize blockchain protocols and algorithms to enhance performance and scalability.\nStay up to date with advancements in blockchain technology and identify opportunities for integration.\nConduct code reviews to maintain high-quality standards and secure coding practices.\nParticipate in research and development initiatives to innovate within the blockchain space.\nProvide mentorship to junior developers and contribute to building a strong development team.\nCreate detailed technical documentation for projects and solutions.', 
'Contribute to building the future of financial technologies through blockchain innovation.\nBe part of a cutting-edge team dedicated to revolutionizing the fintech industry.\nThis role offers an unparalleled opportunity to work on transformative projects and advance your career.', 
'321 Raffles Place', 
'Mon-Fri 9am-6pm', 
'Win Technologies', 
NULL, 
8500.00, 
(SELECT userId FROM users WHERE email = 'agent4@agency4.com')),

('Teacher', 
'Design and deliver engaging lesson plans that inspire curiosity and foster critical thinking among students.\nEvaluate student progress and provide feedback to support their academic development.\nIncorporate technology and innovative teaching methods to enhance learning experiences.\nCreate a positive and inclusive classroom environment that encourages participation.\nCollaborate with colleagues to develop and refine curriculum materials.\nCommunicate regularly with parents to discuss student performance and address concerns.\nStay informed about the latest educational research and implement best practices.\nParticipate in professional development opportunities to continuously improve teaching skills.', 
'Be part of an educational movement empowering young minds to achieve their fullest potential.\nThis role provides the opportunity to shape the future by nurturing the next generation of leaders.\nJoin a dedicated team committed to academic excellence and holistic education.', 
'654 Bukit Timah', 
'Mon-Fri 8am-4pm', 
'My First Skool', 
NULL, 
5000.00, 
(SELECT userId FROM users WHERE email = 'agent5@agency5.com')),

('Curriculum Designer', 
'Develop comprehensive curriculum materials that cater to diverse learning needs and educational standards.\nCollaborate with educators to create engaging and effective lesson plans.\nIncorporate modern teaching methodologies and digital tools into curriculum designs.\nEnsure alignment with national education standards and learning objectives.\nConduct research to identify emerging trends and integrate them into educational programs.\nProvide training to teachers on the implementation of newly designed curriculums.\nEvaluate and refine curriculums based on feedback and student performance metrics.\nPrepare detailed documentation and reports to support curriculum development initiatives.', 
'Collaborate with educators to innovate teaching practices and enhance learning outcomes.\nThis role allows you to contribute meaningfully to the educational landscape in Singapore.\nBe part of a passionate team dedicated to fostering a love for learning and academic success.', 
'654 Bukit Timah', 
'Mon-Fri 8am-4pm', 
'Designer Clubs', 
NULL, 
5500.00, 
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
