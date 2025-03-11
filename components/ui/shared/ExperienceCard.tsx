interface ExperienceCardProps {
  fromYear: string | number;
  toYear: string | number;
  role: string;
  company: string;
  icon: React.ReactNode;
  description: string;
  location: string,
}

const ExperienceCard: React.FC<ExperienceCardProps> = ({
  fromYear,
  toYear,
  role,
  company,
  icon,
  description,
  location
}) => {
  return (
    <div className="resume-box mb-1">
      <ul>
        <li>
          <div className="icon">{icon}</div>
          <span className="time">
            {fromYear} - {toYear} | &nbsp;<strong>{location}</strong>
          </span>
          <h5>
            {role} - {company}
          </h5>
          <p>{description}</p>
        </li>
      </ul>
    </div>
  );
};

export default ExperienceCard;
