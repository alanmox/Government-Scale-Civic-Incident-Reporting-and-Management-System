# GOVERNMENT-SCALE CIVIC INCIDENT REPORTING AND MANAGEMENT SYSTEM (GCIRMS)

## 1. COVER PAGE
**Project Title:** Government-Scale Civic Incident Reporting and Management System (GCIRMS)  
**Student Name:** [Your Name Here]  
**Registration Number:** [Your Registration Number]  
**Institution:** [Your University]  
**Programme:** B.Sc. Software Engineering / Computer Science  
**Course:** [Course Name]  
**Lecturer:** [Lecturer Name]  
**Submission Date:** July 2026  

---

## 2. DECLARATION
I, **[Your Name]**, declare that this project, titled "Government-Scale Civic Incident Reporting and Management System," is my original work and has not been submitted for any degree or examination at any other university. All sources of information and technologies used have been properly acknowledged.

---

## 3. ACKNOWLEDGEMENT
I would like to express my sincere gratitude to my supervisor, [Lecturer Name], for their invaluable guidance and support throughout this project. I also extend my thanks to the faculty members of the Computer Science department for their academic instructions which provided the foundation for this enterprise-grade system.

---

## 4. ABSTRACT
The Government-Scale Civic Incident Reporting and Management System (GCIRMS) is a specialized web-based application designed to bridge the communication gap between citizens and government agencies. It enables citizens to report civic issues (e.g., broken infrastructure, water shortages, safety hazards) and provides government institutions with a robust, automated workflow engine to route, verify, assign, and resolve these incidents within strict Service Level Agreements (SLAs). 

Built entirely using pure PHP 8+ Object-Oriented Programming without external frameworks, the system demonstrates mastery of enterprise software architecture, manual MVC pattern implementation, strict security enforcement, and highly relational database design in MySQL.

---

## 5. PROBLEM STATEMENT
Currently, citizens face significant hurdles when attempting to report civic issues to relevant authorities. The lack of a centralized digital reporting mechanism results in:
1. **Misrouting of reports** to the wrong departments.
2. **Lack of transparency** for the citizen regarding the status of their report.
3. **Inefficient manual workflows** leading to delayed resolution times and SLA breaches.
4. **Poor resource allocation** by government agencies due to a lack of centralized analytical data.

---

## 6. OBJECTIVES
### General Objective
To design, develop, and deploy a scalable, secure, and centralized civic incident management platform that automates the reporting, routing, and resolution workflow for government infrastructure issues.

### Specific Objectives
1. Implement a **Role-Based Access Control (RBAC)** architecture to manage Citizens, Verification Officers, Agency Supervisors, and Administrators securely.
2. Develop a strict **State Machine Workflow Engine** to guarantee that incident reports follow a mandatory lifecycle (Submitted -> Verified -> Assigned -> Resolved).
3. Automate **Agency Routing** based on incident categories (e.g., Water issues route automatically to DAWASA, Road issues to TANROADS).
4. Implement a **Work Order Subsystem** enabling officers to log progress, costs, and internal notes.
5. Provide **Real-time Analytics Dashboards** customized for different administrative tiers.

---

## 7. SYSTEM ARCHITECTURE & METHODOLOGY

### 7.1 Architecture Design
The system abandons commercial frameworks (like Laravel) to demonstrate core software engineering competence. It utilizes a **Manual MVC (Model-View-Controller)** architecture implemented from scratch:
* **Front Controller (`index.php`)**: All requests are routed through a single entry point.
* **Router & Middleware Pipeline**: Custom routing engine parsing URLs and executing security middleware (CSRF, Auth) before reaching controllers.
* **Repository Pattern**: Database operations are strictly abstracted away from business logic. Models never query the database directly.
* **Service Layer**: Complex business rules (Workflows, SLAs, Routing, File Uploads) are handled by dedicated Service classes.

### 7.2 Security by Design
* **Authentication**: Argon2ID hashing, session regeneration, and AES-256-GCM encryption for sensitive internal files.
* **Database**: PDO Prepared Statements exclusively used to eliminate SQL Injection. 
* **Uploads**: Strict MIME-type binary validation (`finfo_file`) to prevent malicious executable uploads. Files are stored outside the public web root and served via secure proxy.

---

## 8. CORE MODULES IMPLEMENTED
1. **Infrastructure & Security**: Custom routing, middleware chains, and session management.
2. **User & Organization Management**: Multi-tier hierarchy (Agencies, Departments, Roles).
3. **Incident Reporting**: Multi-category reporting with SLA tracking and geolocation data.
4. **Workflow & Routing Engine**: Immutable database logging of all state transitions and automated assignment logic.
5. **Officer Work Orders**: Granular tracking of task completion percentage, internal communications, and cost estimation.
6. **REST API**: JWT-ready Bearer token endpoints for external mobile application integration.

---

## 9. CONCLUSION
The GCIRMS project successfully fulfills the requirements of a modern, enterprise-grade government platform. By developing the MVC framework from scratch, the project demonstrates a profound understanding of HTTP lifecycles, Object-Oriented design patterns (Singleton, Factory, Repository, Strategy), and advanced relational database normalization. The platform is scalable, secure, and ready for deployment on cloud infrastructure (AWS EC2).

---

## 10. FUTURE ENHANCEMENTS
* Implementation of SMS Notifications via local telecom gateways.
* Full integration of interactive Leaflet.js maps for spatial density clustering of incidents.
* Machine Learning layer to predict SLA breach probabilities based on historical agency performance data.
