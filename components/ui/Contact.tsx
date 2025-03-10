import ContactCard from "./shared/ContactCard"
import { contacts } from "@/constants/const"

const Contact = () => {
  return (
    <section id="contact" data-scroll-index="4" className="section contactus-section gray-bg-1">
    <div className="container">
        <div className="row gy-5">
            <div className="col-lg-5 d-flex">
                <div className="d-flex flex-column w-100">
                    <div className="contact-info">
                        <h3>Let&lsquo;s Discuss Your Project</h3>
                       {contacts.map((contact, index) => (
                                     <ContactCard
                                     key={index}
                                     cta={contact.cta}
                                     description={contact.description}
                                    icon={contact.icon}
                                     />
                                   ))}
                        {/* <div className="google-map mt-5">
                            <div className="ratio ratio-21x9">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3151.840107317064!2d144.955925!3d-37.817214!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xb6899234e561db11!2sEnvato!5e0!3m2!1sen!2sin!4v1520156366883" allowFullScreen={false}></iframe>
                            </div>
                        </div> */}
                    </div>
                </div>
            </div>
            <div className="col-lg-7 ps-xl-5">
                <div className="contact-form">
                    <div className="contact-head">
                        <h4>Got Ideas? We&lsquo;ve got the skills. Let&lsquo;s team up.</h4>
                        <p>Tell us more about yourself and what you&lsquo;re got in mind.</p>
                    </div>
                    <form id="contact-form" method="POST">
                        <div className="row gx-3 gy-4">
                            <div className="col-md-6">
                                <div className="form-group">
                                    <label className="form-label">First name</label>
                                    <input name="Name" id="name" placeholder="Name *" className="form-control" type="text"/>
                                </div>
                            </div>
                            <div className="col-md-6">
                                <div className="form-group">
                                    <label className="form-label">Your Email</label>
                                    <input name="Email" id="email" placeholder="Email *" className="form-control" type="email"/>
                                </div>
                            </div>
                            <div className="col-12">
                                <div className="form-group">
                                    <label className="form-label">Subject</label>
                                    <input name="Subject" id="subject" placeholder="Subject *" className="form-control" type="text"/>
                                </div>
                            </div>
                            <div className="col-md-12">
                                <div className="form-group">
                                    <label className="form-label">Your message</label>
                                    <textarea name="message" id="message" placeholder="Your message *" rows={4} className="form-control"></textarea>
                                </div>
                            </div>
                            <div className="col-md-12">
                                <div className="send">
                                    <button className="px-btn w-100" type="button" value="Send"> Send Message</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
  )
}

export default Contact
