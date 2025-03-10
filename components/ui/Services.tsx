import { services } from "@/constants/const";
import ServiceCard from "./shared/ServiceCard";

const Services = () => {
  return (
    <section
      id='services'
      data-scroll-index='2'
      className='section services-section'
    >
      <div className='container'>
        <div className='section-heading'>
          <h3>
            <span>MY SERVICES</span>
          </h3>
        </div>
    <div className="row gy-4">
        {services.map((service, index) => (
          <ServiceCard
          key={index}
            title={service.title}
            description={service.description}
            icon={service.icon}
          />
        ))}
      </div>
      </div>
    </section>
  );
};

export default Services;
