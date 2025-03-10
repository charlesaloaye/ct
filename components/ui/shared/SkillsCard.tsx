interface SkillsCardProps {
  skill: string;
  min: number;
  max: number;
  now: number;
  bgColor: string;
}

const SkillsCard: React.FC<SkillsCardProps> = ({ skill, min, max, now, bgColor }) => {
  return (
    <div className="skills">
      <div className="skill-lt">
        <h6 className="dark-color">{skill}</h6>
        <div className="skill-bar">
          <div
            className={`skill-bar-in ${bgColor}`}
            role="progressbar"
            aria-valuenow={now}
            aria-valuemin={min}
            aria-valuemax={max}
            style={{ width: `${now}%` }} // Ensuring the progress bar reflects the percentage
          >
            <span>{now}%</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SkillsCard;
