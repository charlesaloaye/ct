const SkillsCard = ({ skill, min, max, now, bgColor }) => {
  return (
    <div className='skills'>
      <div className='skill-lt'>
        <h6 className='dark-color'>{skill}</h6>
        <div className='skill-bar'>
          <div
            className={`skill-bar-in ${bgColor}`}
            role='progressbar'
            aria-valuenow={now}
            aria-valuemin={min}
            aria-valuemax={max}
          >
            <span>{now}%</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SkillsCard;
