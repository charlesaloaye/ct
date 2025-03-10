
import SocialCard from '@/components/ui/shared/SocialCard'
import {experience, skills, socials} from '@/constants/const'
import ExperienceCard from './shared/ExperienceCard'
import SkillsCard from './shared/SkillsCard'
// import Image from 'next/image'
const About = () => {
  return (
    <section id="about" data-scroll-index="1" className="section about-section gray-bg-1">
    <div className="container">
        <div className="about-me">
            <div className="row align-items-start">
                <div className="col-lg-5 pb-4 pb-lg-0">
                    <div className="title-01">
                        <span>About Me</span>
                    </div>
                    <div className="about-me-text pb-5">
                        <h3>My Name Is {process.env.NEXT_PUBLIC_APP_NAME}</h3>
                        <h5><span>I Am Available</span> For Web and Mobile Applications Projects</h5>
                        
                        <p>I’m a passionate software developer with 8+ years of experience building user-friendly, scalable, and visually appealing applications. Proficient in NextJs, React, PHP, and PG, MySQL, Mongo, and API, I thrive on transforming complex challenges into elegant solutions. Let’s build something amazing together!.</p>
                        <div className="row pt-2">
                            <div className="col-auto">
                                <div className="a-count">
                                    <span className="count">{process.env.NEXT_PUBLIC_PROJECTS}</span>
                                    <div className="a-count-text">Projects <br/>Completed.</div>
                                </div>
                            </div>
                            <div className="col-auto">
                                <div className="a-count">
                                    <span className="count">{process.env.NEXT_PUBLIC_CLIENTS}</span>
                                    <div className="a-count-text">Satisfied <br/>Clients.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div className="title-01 mt-5">
                        <span>Experience</span>
                    </div>
                {experience.map((exp, index) => <ExperienceCard key={index} company={exp.company} description={exp.description} fromYear={exp.fromYear} icon={exp.icon} role={exp.role} toYear={exp.toYear}/>)}
                </div>
                <div className="col-lg-7 sticky-lg-top ps-xl-5">
                    <div className="row align-items-start">
                        <div className="col-md-7 py-4">
                            <div className="about-me-img">
                                <img src="/assets/img/charlestechy1.jpg" title={process.env.NEXT_PUBLIC_APP_NAME} 
                                alt={process.env.NEXT_PUBLIC_APP_NAME || 'Banner'}/>
                            </div>
                        </div>
                        <div className="col-md-5 py-4">
                            <div className="about-content">
                                {socials.map((social, index)=>  <SocialCard key={index} icon={social.icon} title={social.title} url={social.url} target={'_blank'} />)}
                            </div>
                        </div>
                        <div className="col-12 py-4">
                            <div className="title-01">
                                <span>Skills</span>
                            </div>
                            {skills.map((skill, index) => <SkillsCard bgColor={skill.color} key={index} skill={skill.title} max={skill.max} min={skill.min} now={skill.now}/>)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
  )
}

export default About
