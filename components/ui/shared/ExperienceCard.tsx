const ExperienceCard = ({fromYear, toYear, role, company, icon, description}) => {
  return (
    <div className='resume-box'>
      <ul>
        <li>
          <div className='icon'>
            {icon}
          </div>
          <span className='time'>{fromYear} - {toYear}</span>
          <h5>{role} - {company}</h5>
          <p>
           {description}
          </p>
        </li>
      </ul>
    </div>
  );
};

export default ExperienceCard;
