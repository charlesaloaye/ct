interface ExperienceCardProps {
  fromYear: string | number;
  toYear: string | number;
  role: string;
  company: string;
  icon: React.ReactNode;
  description: string;
}

const ExperienceCard: React.FC<ExperienceCardProps> = ({
  fromYear,
  toYear,
  role,
  company,
  icon,
  description,
}) => {
  return (
    <div className="resume-box">
      <ul>
        <li>
          <div className="icon">{icon}</div>
          <span className="time">
            {fromYear} - {toYear}
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
