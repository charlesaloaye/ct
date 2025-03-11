import {
  BiBriefcase,
  BiCart,
  BiCloud,
  BiCloudLightning,
  BiCurrentLocation,
  BiHome,
  BiInfoCircle,
  BiLaptop,
  BiLink,
  BiLogoFacebook,
  BiLogoInstagram,
  BiLogoTwitter,
  BiLogoWhatsapp,
  BiMailSend,
  BiMobile,
  BiPhoneCall,
  BiShield,
  BiSolidDashboard,
  BiSolidData,
  BiSupport,
  BiSync,
  BiUser,
} from "react-icons/bi";
// Menu
export const menu  = [
  {
    title: "Home",
    url: "#home",
    icon: <BiHome />,
  },
  {
    title: "About",
    url: "#about",
    icon: <BiUser />,
  },
  {
    title: "Services",
    url: "#services",
    icon: <BiBriefcase />,
  },
  {
    title: "Portfolio",
    url: "#portfolio",
    icon: <BiSolidDashboard />,
  },
  {
    title: "Contact",
    url: "#contact",
    icon: <BiPhoneCall />,
  },
];
// Skills
export const skills  = [
  {
    title: "ExpressJs",
    now: 95,
    color: "bg-black",
    min: 90,
    max: 100,
  },
  {
    title: "PHP",
    now: 95,
    color: "bg-black",
    min: 80,
    max: 100,
  },
  {
    title: "MongoDB",
    now: 95,
    color: "bg-black",
    min: 80,
    max: 100,
  },
  {
    title: "MySQL",
    now: 95,
    color: "bg-black",
    min: 80,
    max: 100,
  },
  {
    title: "Bootstrap",
    now: 95,
    color: "bg-black",
    min: 80,
    max: 100,
  },
  {
    title: "ReactJs",
    now: 90,
    color: "bg-black",
    min: 80,
    max: 100,
  },

  {
    title: "NextJs",
    now: 90,
    color: "bg-black",
    min: 90,
    max: 100,
  },
  
  {
    title: "PostgresSQL",
    now: 90,
    color: "bg-black",
    min: 80,
    max: 100,
  },
 
  {
    title: "Tailwind",
    now: 85,
    color: "bg-black",
    min: 80,
    max: 100,
  },
 
];
// Services
export const services  = [
  {
    icon: <BiLaptop size={60} />,
    title: "Web Application Development",
    description:
      "End-to-end development of web applications, handling both frontend (React, Next.js) and backend (Node.js, Express).",
  },
  {
    icon: <BiMobile size={60} />,
    title: "Mobile  Application Development",
    description:
      " Building cross-platform mobile applications using React Native and Expo for iOS and Android.",
  },
  {
    icon: <BiSync size={60} />,
    title: "API Development & Integration",
    description:
      "Designing and implementing RESTful APIs using Node.js, Express, and Next.js for efficient data exchange.",
  },
  {
    icon: <BiSolidData size={60} />,
    title: "Database Design & Management",
    description:
      "Creating and optimizing relational (PostgreSQL, MySQL) and NoSQL (MongoDB) databases with efficient schema design and queries.",
  },
  {
    icon: <BiShield size={60} />,
    title: "Authentication & Security",
    description:
      "mplementing secure authentication using JWT, OAuth, and other authentication strategies to protect user data.",
  },
  {
    icon: <BiCloud size={60} />,
    title: "Cloud Deployment & DevOps",
    description:
      "Deploying applications on cloud platforms like AWS, Vercel, and DigitalOcean, including CI/CD pipelines for smooth deployment.",
  },
  {
    icon: <BiCloudLightning size={60} />,
    title: "Performance Optimization",
    description:
      "Improving application speed, reducing load times, and optimizing code for scalability and efficiency.",
  },
  {
    icon: <BiCart size={60} />,
    title: "E-commerce Development",
    description:
      "Building and integrating online stores using Next.js, Stripe, Paystack, Flutterwave and Shopify APIs for seamless experiences.",
  },
  {
    icon: <BiSupport size={60} />,
    title: "Maintenance & Support",
    description:
      "Providing ongoing updates, bug fixes, and feature enhancements to ensure smooth application functionality.",
  },
];
// Contacts
export const contacts  = [
  {
    cta: "Call Me",
    icon: <BiPhoneCall />,
    description: "Book a consultation today <br/> <strong>081372037803</strong>",
  },
  {
    cta: "Send Email",
    icon: <BiMailSend />,
    description: "Send mail today <br/> <strong>charlestechy0@gmail.com</strong>",
  },
  {
    cta: "Address",
    icon: <BiCurrentLocation />,
    description: "<br/> <strong>127.0.0.1</strong>",
  },
];
// Social Media
export const socials  = [
  {
    id: 1,
    title: "I am on X",
    url: "//x.com/sedenucharles",
    target: "_blank",
    icon: <BiLogoTwitter />,
  },
  {
    id: 2,
    title: "I am on Instagram",
    url: "//instagram.com/sedenucharles",
    target: "_blank",
    icon: <BiLogoInstagram />,
  },
  {
    id: 3,
    title: "I am on Facebook",
    url: "//m.me/sedenu.charles",
    target: "_blank",
    icon: <BiLogoFacebook />,
  },
  {
    id: 4,
    title: "I am on Whatsaap",
    url: "//wa.me/+2348137203803",
    target: "_blank",
    icon: <BiLogoWhatsapp />,
  },
];
// Projects
export const projects  = [
  {
    title: "School Management System",
    image: "/assets/projects/eduabroad.png",
    description: "",
    url: "//eduabroad.ng",
    icon: <BiLink />,
  },
  {
    title: "Delta State University Payslip Management system",
    image: "/assets/projects/delta-state-university.png",
    description: "",
    url: "//bursarydelsu.net",
    icon: <BiLink />,
  },
  {
    title: "NYSC CDS Benue Website",
    image: "/assets/projects/cdsbenue.png",
    description: "",
    url: "//cdsbenue.ng",
    icon: <BiLink />,
  },

  {
    title: "Shipping and Logistics Website",
    image: "/assets/projects/evergreen.png",
    description: "",
    url: "//evergreenupdates.co.uk",
    icon: <BiLink />,
  },

  {
    title: "Ecommerce Management System",
    image: "/assets/projects/exclusive-world.png",
    description: "",
    url: "#",
    icon: <BiLink />,
  },

  {
    title: "Investment Management System",
    image: "/assets/projects/wavecresthome.png",
    description: "",
    url: "//wavecresttradinginstitute.com",
    icon: <BiLink />,
  },
  {
    title: "Blog/Content Management System",
    image: "/assets/projects/storyteller.png",
    description: "",
    url: "//storyteller.ng",
    icon: <BiLink />,
  },

  {
    title: "Logistics Website",
    image: "/assets/projects/esf.png",
    description: "",
    url: "#",
    icon: <BiLink />,
  },
  // {
  //   title: "School Management System",
  //   image: "/assets/projects/eduabroad.png",
  //   description: "",
  //   url: "//eduabroad.ng",
  //   icon: <BiLink />,
  // },

  {
    title: "Investment Management System",
    image: "/assets/projects/anglia-ruskin-university.png",
    description: "",
    url: "#",
    icon: <BiLink />,
  },
  {
    title: "Portfolio Website",
    image: "/assets/projects/charlestechy.png",
    description: "",
    url: "#",
    icon: <BiLink />,
  },
  {
    title: "Mobile Telecom Website",
    image: "/assets/projects/greenfoot.png",
    description: "",
    url: "//greenfoot.com.ng",
    icon: <BiLink />,
  },
  {
    title: "Dashboard Design",
    image: "/assets/projects/wavedashboard.png",
    description: "",
    url: "//wavecresttradinginstitute.com/dashboard",
    icon: <BiLink />,
  },
];
// Work and Experience
export const experience  = [
  {
    fromYear: "2024",
    toYear: "2025",
    company: "Evergreen Updates Ltd.",
    role: "Software Developer",
    description: `Built robust backend systems to automate key logistics processes such as inventory
management, delivery scheduling, and billing.`,
    icon: <BiInfoCircle/>,
    location: 'London, England'
  },
  {
    fromYear: "2024",
    toYear: "Present",
    company: "RCCG Faith Foundation Fellowship Centre.",
    role: "Web Developer",
    description: `Created and implemented an event management system that allows members to register
for church events, conferences, and retreats online and also Integrated secure online giving and donation systems.`,
    icon: <BiInfoCircle/>,
    location: 'Essex, England'
  },
  {
    fromYear: "2022",
    toYear: "Present",
    company: "Delta State University, Nigeria.",
    role: "Software Developer",
    description: `Designed and implemented a web-based system to digitise financial operations within the
Bursary Department, significantly improving accuracy and reducing manual errors.`,
    icon: <BiInfoCircle/>,
    location: 'Delta State, Nigeria'
  },
  {
    fromYear: "2019",
    toYear: "Present",
    company: "WAVECREST TRADING INSTITUTE",
    role: "Software Developer",
    description: `Designed and implemented a responsive website that enhanced the institute's online
presence, making it easier for students and stakeholders to access information about
courses, events, resources.`,
    icon: <BiInfoCircle/>,
    location: 'Ekiti State, Nigeria'
  },
  {
    fromYear: "2020",
    toYear: "Present",
    company: "Hamplus Technologies [Hamplus Hub].",
    role: "Chief Technology Officer",
    description: `Spearheaded the design and implementation of dynamic and user-friendly web
applications. Leveraged modern technologies like React.js, Express, and MYSQL to create
seamless, scalable, and secure applications tailored to client requirements.
`,
    icon: <BiInfoCircle/>,
    location: 'Delta State, Nigeria'
  },
  {
    fromYear: "2023",
    toYear: "2024",
    company: "Aiivon Global Innovations Limited",
    role: "Course Facilitator",
    description: `Designed and implemented a robust web development curriculum that covers essential
technologies such as HTML, CSS, JavaScript, React, MYSQL, and PHP, ensuring students
are well-equipped with both front-end and back-end development skills.`,
    icon: <BiInfoCircle/>,
    location: 'Benue State, Nigeria'
  },

  {
    fromYear: "2020",
    toYear: "2022",
    company: "Edu Abroad Limited",
    role: "Web Developer",
    description: `Developed an intuitive student application portal that streamlined the submission of
documents, course selections, and application tracking. This significantly reduced the
administrative burden and improved the user experience for prospective students.`,
    icon: <BiInfoCircle/>,
    location: 'Edo State, Nigeria'
  },

  {
    fromYear: "2019",
    toYear: "2020",
    company: "Global Success Ministries",
    role: "Web Developer",
    description: `Designed and developed a user-friendly and responsive website that accurately reflects
the ministry’s mission and vision. The website enabled easier access to resources, events,
services and secure online payment systems.`,
    icon: <BiInfoCircle/>,
    location: 'Lagos State, Nigeria'
  },

  {
    fromYear: "2018",
    toYear: "2019",
    company: "Millennium Energy, NG",
    role: "Wordpress Developer",
    description: `Developed front-end websites using WordPress, incorporating SEO best practices for
optimal site performance.`,
    icon: <BiInfoCircle/>,
    location: 'Delta State, Nigeria'
  },

];
// Testimonies
export const testimonies  = [
  {
    client: "Customer Name",
    title: "Thanks for the service",
    review: "nice website",
    role: "CEO at MySite",
    image: "/globe.svg",
  },
  {
    client: "Customer Name",
    title: "Thanks for the service",
    review: "nice website",
    role: "CEO at MySite",
    image: "/globe.svg",
  },
  {
    client: "Customer Name",
    title: "Thanks for the service",
    review: "nice website",
    role: "CEO at MySite",
    image: "/globe.svg",
  },
  {
    client: "Customer Name",
    title: "Thanks for the service",
    review: "nice website",
    role: "CEO at MySite",
    image: "/globe.svg",
  },
];
